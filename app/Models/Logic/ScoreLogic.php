<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 19-06-06
 * Time: 上午11:35
 */

namespace App\Models\Logic;


use App\Models\Data\ScoreData;
use App\Models\Data\UserData;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\DbException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;

/**
 * 用户积分逻辑层
 * @Bean()
 * @uses  ScoreLogic
 * @author Nihuan
 * @package App\Models\Logic
 */
class ScoreLogic
{

    /**
     * @Inject("appRedis")
     * @var Redis
     */
    private $appRedis;

    /**
     * @Inject()
     * @var ScoreData
     */
    private $scoreData;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @param int $user_id
     * @param string $rule_key
     * @param array $attr
     * @return int
     * @throws DbException
     */
    public function user_score_increase(int $user_id, string $rule_key, array $attr)
    {
        $now_time = time();
        $code = 0;
        $record_id = 0;
        $isSafePrice = 0;
        $uid = $user_id;
        if(empty($uid)){
            $code = -1;
            Log::info('用户不存在:' . $rule_key . '=>' . json_encode($attr,JSON_UNESCAPED_UNICODE));
        }else{
            $rule_list = $this->scoreData->get_rule_list();
            if(isset($rule_list[$rule_key])){
                $rule_info = $rule_list[$rule_key];
                $month_limit = $rule_info['month_limit'] * $rule_info['value'];
                $now_score = $rule_info['value']; //当前规则积分
                $ratio = 1;
               $isUserStrength = $this->userData->getIsUserStrength($user_id);

               //获取用户当前积分
                $current_score = 0;
                $current_level = 0;
                $user_score = $this->scoreData->getUserScore($user_id);
               if(!empty($user_score)){
                   $current_score = $user_score['scoreValue'];
                   $current_level = (int)$user_score['levelId'];
               }

                //实力值记录
                $score_get_record_data = [
                    'user_id' => $user_id,
                    'opt_user_id' => isset($attr['opt_id']) ? $attr['opt_id'] : $user_id,
                    'opt_user_type' => isset($attr['from_type']) ? $attr['from_type'] : 1,//1:app用户 2:后台用户
                    'get_rule_id' => (int)$rule_info['id'],
                    'score_value' => $now_score,
                    'old_score' => $current_score,
                    'new_score' => $current_score,
                    'title'=> $rule_info['rule_name'],
                    'desc' => $rule_info['rule_desc'] . ", 获取".$now_score.'分',
                    'add_time' => $now_time,
                    'product_id' => isset($attr['pro_id']) ? (int)$attr['pro_id'] : 0,
                    'order_num' => isset($attr['order_num']) ? $attr['order_num'] : '',
                    'is_valid' => 0,
                    'expire_time'=>strtotime("+4 month",strtotime(date('Y-m-01',$now_time))),
                ];

               $illegal_record = 1;
               switch ($rule_key){
                   //上传sku
                   case 'seller_publish_product':
                       $record_check = $this->scoreData->getProScoreRecord($user_id,$attr['pro_id'],$rule_info['id']);
                       if($record_check['is_passed'] != 1){
                           $illegal_record = 0;
                           $code = 404;
                       }

                       //实商加速
                       if($isUserStrength || $current_level >= 4){
                           $ratio = $this->userData->getSetting('4888_strength_seller_product_score_ratio');
                           $ratio = $ratio ? $ratio : 1;
                           $month_limit = $month_limit * $ratio;
                           $now_score = $now_score * $ratio;
                       }

                       $score_get_record_data['score_value'] = $now_score;
                       $score_get_record_data['desc'] = $rule_info['rule_desc'] . ", 获取".$now_score.'分';
                       if($month_limit > $record_check['score_total']){
                            $score_get_record_data['is_valid'] = 1;
                            $score_get_record_data['new_score'] = $current_score + $now_score;
                       }
                       break;

                   //有效售出订单
                   case 'seller_take_order':
                       //实商加速
                       if($isUserStrength || $current_level >= 4){
                           $ratio = $this->userData->getSetting('4888_strength_seller_product_score_ratio');
                           $ratio = $ratio ? $ratio : 1;
                       }
                       $order_score = $this->order_score_calculate($user_id,$attr,$rule_info,$ratio);
                       if($order_score['is_passed'] != 1){
                           $illegal_record = 0;
                       }
                       $score_get_record_data['new_score'] = $current_score + $order_score['score_get_record']['score_value'];
                       $score_get_record_data = array_merge($score_get_record_data,$order_score['score_get_record']);
                       break;

                   //开通实力商家
                   case 'seller_deposit':
                        $strength_Score = $this->user_sterngth_score_calculate($user_id,$attr,$rule_info);
                       if($strength_Score['is_passed'] != 1){
                           $illegal_record = 0;
                       }
                       if($strength_Score['record_id'] > 0){
                           $score_get_record_data = $strength_Score['score_get_record_data'];
                           $record_id = (int)$strength_Score['record_id'];
                       }else{
                           $score_get_record_data['new_score'] = $current_score + $now_score;
                           $score_get_record_data = array_merge($score_get_record_data,$strength_Score['score_get_record_data']);
                       }
                       break;

                   //缴纳保证金
                   case 'seller_safe_price':
                       $safe_price_score = $this->safe_price_score_calculate($user_id,$attr,$rule_info);
                       if($safe_price_score['is_passed'] != 1){
                           $illegal_record = 0;
                       }
                       $isSafePrice = 1;
                       $score_get_record_data = array_merge($score_get_record_data,$safe_price_score['score_get_record_data']);
                       break;

                    //供应商报价
                   case 'seller_offer':
                       $offer_score = $this->scoreData->getOfferScoreRecord($user_id,$attr['offer_id'],$rule_info['id']);
                       if($rule_info['month_limit'] > $offer_score['offer_count'] && $offer_score['is_passed'] == 1) {
                           $score_get_record_data['product_id'] = 0;
                           $score_get_record_data['new_score'] = $current_score + $now_score;
                           $score_get_record_data['is_valid'] = 1;
                           $score_get_record_data['desc'] = $rule_info['rule_desc'] . ", 获取" . $now_score . '分' . ',报价id:' . $attr['offer_id'];
                       }else{
                           $illegal_record = 0;
                       }
                       break;
               }

               //判断操作是否合法并写入积分记录
                if($illegal_record == 1){
                    $scoreRes = $this->scoreData->saveUserScoreTask($score_get_record_data,$record_id,$isUserStrength,$isSafePrice);
                    if($scoreRes > 0){
                        //存储新的等级排序
//                        $this->appRedis->set('user_' . $user_id . '_up_level',$scoreRes);
                    }
                    $code = 1;
                }elseif($code == 0){
                    $code = -3;
                    Log::info('不符合积分规则:' . $rule_key . '=>' . json_encode($attr,JSON_UNESCAPED_UNICODE));
                }
            }else{
                $code = -2;
                Log::info('规则不存在:' . $rule_key);
            }
        }
        return $code;
    }

    /**
     * @param int $user_id
     * @param string $rule_key
     * @param array $attr
     * @return int
     * @throws DbException
     */
    public function user_score_deduction(int $user_id, string $rule_key, array $attr)
    {
        $code = 0;
        $now_time = time();
        $isSafePrice = 0;
        $rule_list = $this->scoreData->get_rule_list();
        if(isset($rule_list[$rule_key])) {
            $rule_info = $rule_list[$rule_key];
            $now_score = $rule_info['value']; //当前规则积分
            //获取用户当前积分
            $current_score = 0;
            $user_score = $this->scoreData->getUserScore($user_id);
            if(!empty($user_score)){
                $current_score = $user_score['scoreValue'];
            }
            //是否实力商家
            $isUserStrength = $this->userData->getIsUserStrength($user_id);
            Log::info('实商状态:' . $isUserStrength);

            //实力值记录
            $score_get_record_data = [
                'user_id' => $user_id,
                'opt_user_id' => isset($attr['opt_id']) ? $attr['opt_id'] : $user_id,
                'opt_user_type' => isset($attr['from_type']) ? $attr['from_type'] : 1,//1:app用户 2:后台用户
                'get_rule_id' => (int)$rule_info['id'],
                'score_value' => $now_score,
                'old_score' => 0,
                'new_score' => 0,
                'title'=> $rule_info['rule_name'],
                'desc' => $rule_info['rule_desc'] . ", 扣除".$now_score.'分',
                'add_time' => $now_time,
                'product_id' => isset($attr['pro_id']) ? (int)$attr['pro_id'] : 0,
                'order_num' => isset($attr['order_num']) ? $attr['order_num'] : '',
                'is_valid' => 0,
                'expire_time'=>strtotime("+4 month",strtotime(date('Y-m-01',$now_time))),
            ];

            $illegal_record = 1;
            $is_send_notice = 1;
            switch ($rule_key){
                //实力商家到期
                case 'seller_user_strength_experience_expire':
                    $strength_Score = $this->user_strength_score_deduction($attr);
                    Log::info(json_encode($strength_Score));
                    if($strength_Score['is_passed'] != 1){
                        $illegal_record = 0;
                    }
                    $score_get_record_data['new_score'] = $current_score + $now_score > 0 ? $current_score + $now_score : 0;
                    $score_get_record_data = array_merge($score_get_record_data,$strength_Score['score_get_record_data']);
                    Log::info(json_encode($score_get_record_data));
                    break;

                //提取保证金
                case 'seller_safe_price':
                    $safe_price_score = $this->user_safe_price_score_deduction($attr,$rule_info,$user_score);
                    if(isset($attr['send_notice'])){
                        $is_send_notice = $attr['send_notice'];
                    }
                    if($safe_price_score['is_passed'] != 1){
                        $illegal_record = 0;
                    }
                    $isSafePrice = 1;

                    //保证金积分值新旧的差值
                    $new_safe_price_score = 0 - $safe_price_score['score'];
                    if($new_safe_price_score != 0){
                        //保证金积分值新旧的差值
                        $score_get_record_data['score_value'] = $new_safe_price_score;
                        $score_get_record_data['is_valid'] = 1;
                        $score_get_record_data['title'] = '提取保证金';
                        $score_get_record_data['desc'] = '提取保证金，积分' . $new_safe_price_score;
                        $score_get_record_data['expire_time'] = strtotime("2099-12-31 23:59:59");//永远有效
                    }else{
                        $illegal_record = 0;
                    }
                    break;
            }

            //判断操作是否合法并写入积分记录
            Log::info($illegal_record);
            if($illegal_record == 1){
                $scoreRes = $this->scoreData->userScoreDeduction($score_get_record_data,$isUserStrength,$isSafePrice,$is_send_notice);
                if($scoreRes > 0){
                    //存储新的等级排序
//                    $this->appRedis->set('user_' . $user_id . '_up_level',$scoreRes);
                }
                $code = 1;
            }elseif($code == 0){
                $code = -3;
                Log::info('不符合积分规则:' . $rule_key . '=>' . json_encode($attr,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $code = -2;
            Log::info('规则不存在:' . $rule_key);
        }
        return $code;
    }

    /**
     * 订单积分计算
     * @param $user_id
     * @param $attr
     * @param $rule_info
     * @param $ratio
     * @return array
     */
    private function order_score_calculate($user_id,$attr,$rule_info,$ratio)
    {
        $record_check = $this->scoreData->getOrderScoreRecord($user_id,$attr['order_num'],$rule_info['id']);
        $order_price = isset($attr['total_order_price']) ? $attr['total_order_price'] : $record_check['order_price'];
        if($rule_info['is_have_ext_score'] && $rule_info['every_order_price'] && $rule_info['ext_value']){
            //有效售出订单交易金额每50计分，不足50不计分，如49.99=0分，50=1分，99.99=1分
            if($order_price >= $rule_info['every_order_price']){
                //超过50元
                $ext_score_number = floor($order_price/$rule_info['every_order_price']);
                $ext_score = $ext_score_number*$rule_info['ext_value'];
                $rule_value = $rule_info['value'] + $ext_score;

            }else{
                //没有超过50元,给0分
                $rule_value = $rule_info['value'];
            }
        }else{
            $rule_value = $rule_info['value'];
        }
        $user_new_score = 0;
        if($rule_value > 0){
            $user_new_score = intval($rule_value * $ratio);
        }
        $score_get_record_data['is_valid'] = 1;
        $score_get_record_data['score_value'] = $user_new_score;
        $score_get_record_data['desc'] = $rule_info['rule_desc'] . ", 获取".$user_new_score.'分';

        return ['is_passed' => $record_check['is_passed'], 'score_get_record' => $score_get_record_data];
    }

    /**
     * 保证金积分获取
     * @param $user_id
     * @param $attr
     * @param $rule_info
     * @return array
     */
    private function safe_price_score_calculate($user_id,$attr,$rule_info)
    {
        $record_check = $this->scoreData->getSafePriceScoreRecord($user_id,$attr['safe_price']);
        Log::info(json_encode($record_check));
        $order_price = $record_check['total_price'] > 0 ? $record_check['total_price'] : $attr['safe_price'];
        $min_safe_price = $this->userData->getSetting('MIN_SAFE_PRICE');
        if($order_price == 0){
            //无保证金
            $rule_score_value = 0;
        }elseif($rule_info['is_have_ext_score'] && $rule_info['every_order_price'] && $rule_info['ext_value']){
            if($order_price > $min_safe_price){
                //超过指定金额,每超出x元额外加y分
                $ext_score_number = floor(($order_price - $min_safe_price)/$rule_info['every_order_price']);
                $ext_score = $ext_score_number*$rule_info['ext_value'];
                $rule_score_value = $rule_info['value'] + $ext_score;

            }else{
                //200分基础
                $rule_score_value = $rule_info['value'];
            }
        }else{
            $rule_score_value = $rule_info['value'];
        }


        //积分最多300
        $max_safe_price_score = $this->userData->getSetting('max_safe_price_score');
        if($rule_score_value > $max_safe_price_score){
            $rule_score_value = $max_safe_price_score;
        }

        $score_get_record_data['is_valid'] = 1;
        $score_get_record_data['old_score'] = 0;
        $score_get_record_data['score_value'] = $rule_score_value;
        $score_get_record_data['new_score'] = 0;
        $score_get_record_data['desc'] = $rule_info['rule_desc'] . ", 获取".$rule_score_value.'分';
        Log::info(json_encode($score_get_record_data));
        return ['is_passed' => $record_check['is_passed'], 'score_get_record_data' => $score_get_record_data];
    }

    /**
     * 实商积分获取
     * @param $user_id
     * @param $attr
     * @param $rule_info
     * @return array
     */
    private function user_sterngth_score_calculate($user_id,$attr,$rule_info)
    {
        $record_id = 0;
        if(!isset($attr['end_time'])){
            $strength = $this->userData->getUserStrengthInfo($user_id);
            if(!empty($strength)){
                $attr['end_time'] = $strength['end_time'];
            }
        }
        $deposit_score = $this->scoreData->getStrengthScoreRecord($user_id,$rule_info['id']);
        if($deposit_score['record_id'] > 0){
            $score_get_record_data = [
                'user_id' => $user_id,
                'order_num' => '66666666',
                'opt_user_id' => isset($attr['opt_id']) ? $attr['opt_id'] : $user_id,
                'opt_user_type' => isset($attr['from_type']) ? $attr['from_type'] : 1,//1:app用户 2:后台用户
                'expire_time'=> isset($attr['end_time']) ? $attr['end_time'] : $deposit_score['expire_time'],
            ];

            $record_id = $deposit_score['record_id'];
        }else{
            $score_get_record_data['is_valid'] = 1;
            $score_get_record_data['expire_time'] = isset($attr['end_time']) ? $attr['end_time'] : 0;
        }

        return ['is_passed' => $deposit_score['is_passed'],'record_id' => $record_id, 'score_get_record_data' => $score_get_record_data];
    }

    /**
     * 提取保证金对应积分计算
     * @param $attr
     * @param $rule_info
     * @param $user_score
     * @return array
     */
    private function user_safe_price_score_deduction($attr,$rule_info,$user_score)
    {
        $is_passed = 1;
        $order_price = $attr['safe_price'];
        $min_safe_price = $this->userData->getSetting('MIN_SAFE_PRICE');

        if($order_price == 0){
            //无保证金
            $rule_score_value = 0;
        }elseif($rule_info['is_have_ext_score'] && $rule_info['every_order_price'] && $rule_info['ext_value']){
            if($order_price >= $min_safe_price){
                //超过指定金额,每超出x元额外加y分
                $ext_score_number = floor(($order_price - $min_safe_price)/$rule_info['every_order_price']);
                $ext_score = $ext_score_number*$rule_info['ext_value'];
                $rule_score_value = $rule_info['value'] + $ext_score;

            }else{
                //200分基础
                $rule_score_value = $rule_info['value'];
            }
        }else{
            $rule_score_value = $rule_info['value'];
        }

        //积分最多300
        $max_safe_price_score = $this->userData->getSetting('max_safe_price_score');
        if($user_score['baseScoreValue'] == $max_safe_price_score && $order_price == $min_safe_price){
            $rule_score_value = $user_score['baseScoreValue'];
        }
        if($rule_score_value > $max_safe_price_score){
            $rule_score_value = $max_safe_price_score;
        }

        return ['is_passed' => $is_passed, 'score' => $rule_score_value];
    }

    /**
     * 实商过期
     * @param $attr
     * @return array
     */
    private function user_strength_score_deduction($attr)
    {
        $score_get_record_data = [];
        $is_passed = 1;
        if(!isset($attr['id'])){
            $is_passed = 0;
        }
        $strength_info = $this->userData->get_strength_by_id($attr['id']);
        if(!empty($strength_info)){
            $score_get_record_data['is_valid'] = 1;
            $score_get_record_data['expire_time'] = $strength_info['endTime'];
        }

        return ['is_passed' => $is_passed, 'score_get_record_data' => $score_get_record_data];
    }
}