<?php
namespace App\Models\Data;

use App\Models\Dao\OfferDao;
use App\Models\Dao\OrderDao;
use App\Models\Dao\ProductDao;
use App\Models\Dao\ScoreDao;
use App\Models\Dao\UserDao;
use App\Models\Entity\Offer;
use App\Models\Entity\UserScoreGetRecord;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;

/**
 * 积分类Data
 * @Bean()
 * @uses      ScoreData
 */
class ScoreData
{

    /**
     * @Inject()
     * @var ScoreDao
     */
    private $scoreDao;

    /**
     * @Inject()
     * @var ProductDao
     */
    private $productDao;

    /**
     * @Inject()
     * @var OrderDao
     */
    private $orderDao;

    /**
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * 积分规则获取
     * @return array
     */
    public function get_rule_list()
    {
        return $this->scoreDao->getScoreRuleList();
    }

    /**
     * 当月产品总分获取
     * @param int $user_id 用户id
     * @param int $pro_id 产品id
     * @param int $rule_id 产品积分规则id
     * @return array
     */
    public function getProScoreRecord(int $user_id,int $pro_id,int $rule_id)
    {
        $is_passed = 1;
        $scoreTotal = 0;
        $product = $this->productDao->getProductInfoByPid($pro_id);
        if(empty($product)){
            $is_passed = 0;
        }else{
            $params = [
                'get_rule_id' => $rule_id,
                'product_id' => $pro_id,
                'user_id' => $user_id
            ];
            $proScoreRec = $this->scoreDao->getScoreCountByParams($params);
            if($proScoreRec > 0){
                $is_passed = 0;
            }else{
                $add_month = date('Y-m',$product['addTime']);
                if($product['delStatus'] == 1){
                    $scoreTotal = $this->scoreDao->getProScoreSum($user_id,$add_month,$rule_id);
                }
            }
        }
        return ['is_passed' => $is_passed,'score_total' => $scoreTotal];
    }

    /**
     * 当前用户总积分
     * @param $user_id
     * @return mixed
     */
    public function getUserScore($user_id)
    {
        return $this->scoreDao->getUserScore($user_id);
    }

    /**
     * 当日报价积分记录获取
     * @param int $user_id 用户id
     * @param int $offer_id 报价id
     * @param int $rule_id 报价积分规则id
     * @return array
     */
    public function getOfferScoreRecord(int $user_id,int $offer_id, int $rule_id)
    {
        $is_passed = 1;
        $scoreCount = 0;
        $offer = Offer::findOne(['offerer_id' => $user_id, 'offer_id' => $offer_id])->getResult();
        if(!empty($offer)){
            $params = [
                'get_rule_id' => $rule_id,
                'product_id' => $offer_id,
                'user_id' => $user_id
            ];
            $offerScoreRec = $this->scoreDao->getScoreCountByParams($params);
            if($offerScoreRec > 0) {
                $is_passed = 0;
            }else{
                $start_time = strtotime(date('Y-m-d',$offer['offerTime']));
                $end_time = strtotime(date('Y-m-d 23:59:59',$offer['offerTime']));
                $params = [
                    'user_id' => $user_id,
                    'get_rule_id' => $rule_id,
                    ['add_time','>=',$start_time],
                    ['add_time','<',$end_time]
                ];
                $scoreCount = $this->scoreDao->getScoreCountByParams($params);
            }
        }else{
            $is_passed = 0;
        }

        return ['is_passed' => $is_passed, 'offer_count' => $scoreCount];
    }


    /**
     * @param int $user_id 用户id
     * @param string $order_num 订单编号
     * @param int $rule_id 交易积分规则id
     * @return array
     */
    public function getOrderScoreRecord(int $user_id, string $order_num, int $rule_id)
    {
        $is_passed = 1;
        $order_price = 0;
        $order = $this->orderDao->getOrderInfo($order_num,['status','real_get','coupon_price']);
        if(!empty($order) && $order['status'] == 4){
            $params = [
                'user_id' => $user_id,
                'order_num' => $order_num,
                'get_rule_id' => $rule_id
            ];
            $scoreInfo = $this->scoreDao->getScoreRecordByParams($params);
            if(!empty($scoreInfo)){
                $is_passed = 0;
            }

            $order_price = $order['realGet'] + $order['couponPrice'];
        }

        return ['is_passed' => $is_passed, 'order_price' => $order_price];
    }

    /**
     * 实商记录是否存在
     * @param int $user_id 用户id
     * @param int $rule_id 实商规则id
     * @return array
     */
    public function getStrengthScoreRecord(int $user_id,int $rule_id)
    {
        $now_time = time();
        $is_passed = 1;
        $record_id = 0;
        $expire_time = 0;
        $params = [
            'user_id' => $user_id,
            'get_rule_id' => $rule_id,
            ['expire_time','>=',$now_time]
        ];
        $strength_score = $this->scoreDao->getScoreRecordByParams($params);
        if(!empty($strength_score)){
            $record_id = $strength_score['id'];
            $expire_time = $strength_score['expire_time'];
        }

        return ['is_passed' => $is_passed, 'record_id' => $record_id, 'expire_time' => $expire_time];
    }

    /**
     * 保证金额度获取
     * @param int $user_id 用户id
     * @param int $safe_price 新缴纳保证金额
     * @return array
     */
    public function getSafePriceScoreRecord(int $user_id,int $safe_price)
    {
        $is_passed = 1;
        $total_price = $safe_price;
        $user_info = $this->userDao->getUserInfoByUid($user_id);
        if(!empty($user_info) && $user_info['safePrice'] >0){
            $total_price = $user_info['safePrice'] + $safe_price;
        }

        return ['is_passed' => $is_passed, 'total_price' => $total_price];
    }

    /**
     * @param array $data 记录内容
     * @param int $record_id 已存在记录id，目前适用于实商续费
     * @param int $isUserStrength 是否实力商家 用于金牌保级
     * @param int $isSafePrice 是否保证金记录,保证金只更新score表的base_score_value字段
     * @return bool
     * @throws DbException
     */
    public function saveUserScoreTask(array $data, int $record_id, int $isUserStrength,int $isSafePrice)
    {
        return $this->scoreDao->userScoreTask($data,$record_id,$isUserStrength,$isSafePrice);
    }
}
