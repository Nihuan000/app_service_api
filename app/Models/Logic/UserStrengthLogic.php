<?php

namespace App\Models\Logic;

use App\Models\Data\UserData;
use Swoft\App;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
use Swoft\Redis\Redis;

/**
 * 实商处理类
 * @Bean()
 * @uses      UserStrengthLogic
 */
class UserStrengthLogic
{
    /**
     *
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    /**
     * 保证金体验记录id
     * @var int
     */
    private $safe_experience_id = 1;

    /**
     * 用户实商过期处理
     * @param array $strength_info
     * @param int $user_id
     * @param int $is_experience
     * @return int
     * @throws DbException
     */
    public function user_strength_expired(array $strength_info = [], int $user_id = 0,int $is_experience = 0)
    {
        $now_time = time();
        $code = 0;
        if(empty($strength_info) && $user_id == 0){
            $code = -1;
        }else{

            //调用接口请求，判断user_id获取实商信息
            if($user_id > 0){
                $user_strength = $this->userData->getUserStrengthInfo($user_id);
                if(!empty($user_strength)){
                    $strength_info = [
                        'id' => $user_strength['id'],
                        'user_id' => $user_strength['user_id'],
                        'pay_for_open' => $user_strength['pay_for_open'],
                        'end_time' => $user_strength['end_time'],
                    ];
                }
            }

            if(!empty($strength_info)){
                //付费实商未到期,不予处理
                if($strength_info['end_time'] > $now_time && $strength_info['pay_for_open'] == 1){
                    $code = -2;
                    return $code;
                }
                $remark = '实商到期';
                $experienceRes = true;
                //判断是否体验实商
                if($strength_info['pay_for_open'] == 0){
                    //体验记录过期
                    $experience = $this->userData->get_strength_experience_info($strength_info['user_id']);
                    if(!empty($experience)){
                        if($experience['experience_id'] == $this->safe_experience_id){
                            $remark = "提取保证金,实商体验取消";
                        }else{
                            if($strength_info['end_time'] > $now_time && $is_experience == 0){
                                $code = -2; //体验未到期
                                return $code;
                            }
                            $experInfo = $this->userData->get_experience_info($experience['experience_id']);
                            if(!empty($experInfo)){
                                $remark = $experInfo['experience_name'] . '到期';
                            }
                        }
                        $real_experience_day = (strtotime(date('Y-m-d',$now_time)) - strtotime(date('Y-m-d',$experience['add_time'])))/(3600*24);//实际体验的天数

                        //执行体验过期
                        $data = [
                            'remark' => $remark,
                            'real_experience_day' => $real_experience_day
                        ];
                        $experienceRes = $this->userData->strength_receive_expired($experience['id'],$data);
                    }
                }

                //实商记录过期
                $strengthParams = [
                    'is_expire' => 1,
                    'update_time' => $now_time,
                    'remark' => $remark
                ];
                $strengthRes = $this->userData->userStrengthPlus($strength_info['user_id'],$strength_info['id'],$strengthParams);
                write_log(2,'用户id:' . $strength_info['user_id']);
                write_log(2,'体验过期状态:' . $experienceRes);
                write_log(2,'实商过期状态:' . json_encode($strengthRes));
                if($experienceRes && $strengthRes)
                {
                    //用户积分扣除
                    /* @var ScoreLogic $score_logic */
                    $score_logic = App::getBean(ScoreLogic::class);
                    $score_logic->user_score_deduction($strength_info['user_id'],'seller_user_strength_experience_expire',['id' => $strength_info['id']]);


                    //发送实商到期提醒
                    if($strength_info['pay_for_open'] == 1){
                        $this->strength_expire_notice($strength_info['user_id']);
                    }
                    $code = 1;
                }else{
                    $code = -3;
                }
                write_log(2,'实商过期返回状态:' . $code);
                write_log(2,'实商记录id:' . $strength_info['id']);
            }
        }
        return $code;
    }

    /**
     * 开通实商判断
     * @param int $user_id
     * @param string $order_num
     * @param string $experience_key
     * @param int $pay_for_open
     * @return int
     * @throws DbException
     * @throws MysqlException
     */
    public function user_strength_open(int $user_id, string $order_num, string $experience_key, int $pay_for_open)
    {
        $now_time = time();
        $code = 0;
        $experience_data = [];
        $presentation_receive_log_data = [];
        $user_strength_info = $this->userData->getUserStrengthInfo($user_id);
        if(!empty($user_strength_info) && $pay_for_open == 0){
            //实商有效期内，不能再次申请体验
            return -1;
        }

        //付费开通判断
        if($pay_for_open == 1){
            $order_info = $this->userData->get_appreciation_order($order_num);
            if(empty($order_info)){
                //付款订单不存在
                return -2;
            }

            $strengthRes = $this->strength_pay_for_open($order_info,$now_time,$user_id);
            $add_user_strength_data = $strengthRes['add_user_strength_data'];
            $presentation_receive_log_data = $strengthRes['presentation_receive_log_data'];
        }else{
            //体验实商判断
            $experInfo = $this->userData->get_experience_info(0,$experience_key);
            if(empty($experInfo) || !isset($experInfo['reward_day']) || empty($experInfo['reward_day'])){
                //体验活动已下线
                return -3;
            }

            //检验是否已领取过
            $is_receive = $this->userData->get_strength_experience_count($user_id,$experInfo['id']);
            if($is_receive > 0){
                //已领取,请勿重复点击
                return -4;
            }

            $experienceRes = $this->strength_experience_open($user_id,$experInfo,$now_time);
            $add_user_strength_data = $experienceRes['strength_data'];
            $experience_data = $experienceRes['experience_data'];
            if(empty($experience_data)){
                //实商体验领取失败
                return -5;
            }
        }
        Db::beginTransaction();
        if(empty($user_strength_info) && !empty($add_user_strength_data)){
            //新增实力商家
            $change_record_type = 1;
            $strength_id = 0;
        }else{
            //实商续期
            $change_record_type = 2;
            $strength_id = (int)$user_strength_info['id'];
        }

        //实商开通/续期添加
        $res2 = $this->userData->save_user_strength($add_user_strength_data,$strength_id);

        //奖励记录添加
        $presentationRes = true;
        if(!empty($presentation_receive_log_data)){
            $presentationRes = $this->userData->save_appreciation_activity_receive($presentation_receive_log_data);
        }

        //记录实商变更
        $historyRes = true;
        if($change_record_type > 0){
            $change = [
                'old_end_time' => isset($user_strength_info['end_time']) ? $user_strength_info['end_time'] : 0,
                'new_end_time' => isset($add_user_strength_data['end_time']) ? $add_user_strength_data['end_time'] : 0,
                'change_type' => $change_record_type,
                'user_id' => $user_id,
                'opt_user_id' => $user_id
            ];

            /* @var UserLogic $user_logic */
            $user_logic = App::getBean(UserLogic::class);
            $historyRes = $user_logic->strength_history($change);
        }

        $experienceRes = true;
        if(!empty($experience_data)){
            $experienceRes = $this->userData->save_strength_experience_receive($experience_data);
        }

        if($res2 && $presentationRes && $historyRes && $experienceRes){
            Db::commit();

            //积分记录添加
            /* @var ScoreLogic $score_logic */
            $score_logic = App::getBean(ScoreLogic::class);
            $score_logic->user_score_increase($user_id,'seller_deposit',['end_time' => $add_user_strength_data['end_time']]);
            $code = 1;
        }else{
            Db::rollback();
        }
        return $code;
    }

    /**
     * @param int $user_id
     * @return array
     * @throws DbException
     */
    public function strength_expire_notice(int $user_id)
    {
        $notice_history_key = 'over_strength_history_' . date('Y'); //提示历史记录
        $user_ids = [];
        $config = \Swoft::getBean('config');
        $sys_msg = $config->get('sysMsg');
        $receive_status = 0;
        $test_list = $this->userData->getTesters();
        if(in_array($user_id, $test_list) || $this->userData->getSetting('strength_over_switch') == 1){
            $receive_status = 1;
        }
        if($receive_status == 1){
            $history_record = $this->redis->sIsMember($notice_history_key,(string)$user_id);
            if($history_record == 0){
                //发送系统消息
                ################## 消息基本信息开始 #######################
                $extra = $sys_msg;
                $extra['title'] = '实商已到期';
                $extra['msgContent'] = "您的实力商家权限已到期，\n点击重新开通";
                ################## 消息基本信息结束 #######################

                ################## 消息扩展字段开始 #######################
                $extraData['keyword'] = '#点击重新开通#';
                $extraData['type'] = 18;
                $extraData['url'] = $this->userData->getSetting('user_strength_url');
                ################## 消息扩展字段结束 #######################

                $extra['data'] = [$extraData];
                $extra['content'] = "您的实力商家权限已到期，#点击重新开通#";
                $notice['extra'] = $extra;
                $this->redis->sAdd($notice_history_key, (string)$user_id);
                sendInstantMessaging('1', (string)$user_id, json_encode($notice['extra']));
                $user_ids[] = $user_id;
            }else{
                write_log(2,$user_id . '实商到期推送记录已存在');
            }
        }else{
            write_log(2,$user_id . '存在已开通记录，不再提醒');
        }
        if(!empty($user_ids)){
            write_log(2,json_encode($user_ids));
        }
        return $user_ids;
    }


    /**
     * 付费实商开通数据获取
     * @param $order_info
     * @param $now_time
     * @param $user_id
     * @return array
     * @throws DbException
     */
    private function strength_pay_for_open($order_info, $now_time, $user_id)
    {
        $presentation_receive_log_data = [];

        $appreciation_product = $this->userData->get_appreciation_product($order_info['recharge_product_id']);
        $unit_quantity = $appreciation_product['unit_quantity'];//单位数量

        $validity = $appreciation_product['validity'];//有效期
        $service_fee_rate = $appreciation_product['service_fee_rate'];//服务费比例
        $level = 10;

        //当前实商信息
        $user_strength_info = $this->userData->getUserStrengthInfo($user_id);

        if (!empty($user_strength_info) && !empty($user_strength_info['end_time'])) {
            //已开通续费
            $add_user_strength_data = [
                'renew_time' => $user_strength_info['end_time'],
                'update_time' => $now_time,
                'renew_pay_time' => $now_time,
                'level'=>$level,
                'pay_for_open' => 1,
                'end_time' => strtotime(date("Y-m-d",$user_strength_info['end_time'] + $validity * $unit_quantity * 24 * 3600)." 23:59:59"),
            ];
        }else{
            //未开通或已过期
            $add_user_strength_data = [
                'user_id' => $user_id,
                'start_time' => !empty($user_strength_info) && !empty($user_strength_info['start_time']) ? $user_strength_info['start_time'] : time(),
                'end_time' => strtotime(date("Y-m-d",$now_time + $validity * $unit_quantity * 24 * 3600)." 23:59:59"),
                'pay_for_open' => 1,
                'add_time' => $now_time,
                'service_fee_rate'=>$service_fee_rate,
                'level'=>$level,
            ];
        }

        //取消之前的实商体验
        if(!empty($user_strength_info) && $user_strength_info['pay_for_open'] == 0){
            $strength_info = [
                'id' => $user_strength_info['id'],
                'user_id' => $user_strength_info['user_id'],
                'pay_for_open' => $user_strength_info['pay_for_open'],
                'end_time' => $user_strength_info['end_time'],
            ];
            $this->user_strength_expired($strength_info,0,1);
        }

        //付费实商奖励天数计算
        if($appreciation_product['product_key'] == 'strength_seller_one_year'){
            $user_strength_activity = $this->user_strength_activity($user_id, $add_user_strength_data);
            if (!empty($user_strength_activity['strength_data'])){
                $add_user_strength_data = $user_strength_activity['strength_data'];
            }
            $is_user_strength_activity = (int)$user_strength_activity['is_user_strength_activity'];
            $activity_presentation_id = (int)$user_strength_activity['activity_presentation_id'];

            //实商活动奖励日志
            if($is_user_strength_activity && isset($user_strength_activity_id) && !empty($user_strength_activity_id)){
                $presentation_receive_log_data = ['user_id'=>$user_id,'activity_id'=>$user_strength_activity_id,'add_time'=>$now_time];
                if(isset($activity_presentation_id) && !empty($activity_presentation_id)){
                    $presentation_receive_log_data['activity_presentation_id'] = $activity_presentation_id;
                }
            }
        }

        return ['add_user_strength_data' => $add_user_strength_data, 'presentation_receive_log_data' => $presentation_receive_log_data];
    }

    /**
     * 体验实商开通
     * @param $user_id
     * @param $experInfo
     * @param $now_time
     * @return array
     */
    private function strength_experience_open($user_id,$experInfo,$now_time)
    {
        //过期时间计算
        $reward_day = (int)$experInfo['reward_day'];
        $expire_time = strtotime(date('Y-m-d 23:59:59',$now_time + $reward_day * 3600 * 24));

        //体验记录数据
        $experience_data = [
            'user_id' => $user_id,
            'experience_id' => $experInfo['id'],
            'reward_day' => $reward_day,
            'add_time' => $now_time,
            'new_expire_time'=>$expire_time,
        ];

        //实商数据
        $appreciation_product = $this->userData->get_appreciation_product(1);
        $add_user_strength_data = [
            'user_id' => $user_id,
            'start_time' => $now_time,
            'end_time' =>$expire_time,
            'add_time' => $now_time,
            'service_fee_rate'=>$appreciation_product['service_fee_rate'],
            'level'=>10,
            'remark'=>"体验".$reward_day."天实力商家",
        ];

        return ['experience_data' => $experience_data, 'strength_data' => $add_user_strength_data];
    }

    /**
     * 实商活动奖励数据统计
     * @param $user_id
     * @param $strength_data
     * @return array
     */
    private function user_strength_activity($user_id, $strength_data)
    {
        $now_time = time();
        $is_user_strength_activity = false;
        $activity_presentation_id = 0;
        $strength_activity_info = $this->userData->getStrengthActivity($now_time,1);
        if(!empty($strength_activity_info)){
            //活动id
            $user_strength_activity_id = $strength_activity_info['id'];

            //第一天开通奖励
            if($now_time <= $strength_activity_info['spe_presentation_end_time']){
                $spe_presentation_month = $strength_activity_info['spe_presentation_month'];
                $strength_data['end_time'] = strtotime("+{$spe_presentation_month} months",$strength_data['end_time']);
                $is_user_strength_activity = true;
            }

            //保证金奖励
            $user_info = $this->userData->getUserInfo($user_id);
            if($user_info['safePrice'] > 0){
                $safe_price_presentation_info = $this->userData->get_appreciation_presentation($user_strength_activity_id,$user_info['safePrice']);
                if(!empty($safe_price_presentation_info)){
                    //活动奖励id
                    $activity_presentation_id = $safe_price_presentation_info['id'];
                    if(!empty($safe_price_presentation_info['presentation_value']) && !empty($safe_price_presentation_info['presentation_value_type'])){
                        $presentation_value = $safe_price_presentation_info['presentation_value'];
                        $presentation_value_type = $safe_price_presentation_info['presentation_value_type'];

                        if($presentation_value_type == 1){
                            $add_user_strength_data['end_time'] = strtotime("+{$presentation_value} months",$strength_data['end_time']);
                            $is_user_strength_activity = true;
                        }elseif($presentation_value_type == 2){
                            $is_user_strength_activity = true ;
                            $add_user_strength_data['end_time'] = strtotime("+{$presentation_value} days",$strength_data['end_time']);
                        }
                    }
                }
            }
        }

        return ['is_user_strength_activity' => $is_user_strength_activity, 'activity_presentation_id' => $activity_presentation_id, 'strength_data' => $strength_data];
    }
}
