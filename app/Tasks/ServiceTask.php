<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-26
 * Time: 下午6:19
 * Desc: 行业动态定时任务
 */

namespace App\Tasks;

use App\Models\Dao\UserDao;
use App\Models\Data\BuyData;
use App\Models\Data\ProductData;
use App\Models\Entity\User;
use Swoft\Db\Db;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Service task
 *
 * @Task("Service")
 */
class ServiceTask
{
    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * @Inject()
     * @var BuyData
     */
    private $buyData;

    /**
     * @Inject()
     * @var ProductData
     */
    private $productData;

    /**
     * 行业动态
     * @author Nihuan
     * @Scheduled(cron="10 * * * * *")
     * @throws \Swoft\Db\Exception\DbException
     */
    public function industryTask()
    {
        $industry = 'industry_';
        $seller_event = 'seller_';
        $date = date('Y-m-d');
	    $date_time_format = time();
        $redisRes = $this->redis->hGet(INDUSTRY_NEWS_TIME,'industry');
        $last_ansy_time = $redisRes == 0  ? strtotime($date) : (int)$redisRes;
        //采购信息获取,包括新发布/已找到
        $buyResult = Db::query("select t.user_id,t.status,t.is_audit,t.add_time,t.alter_time,sb_user.name,sb_user.city FROM sb_buy as t LEFT JOIN sb_user ON sb_user.user_id = t.user_id WHERE t.alter_time >= ?",[(int)$last_ansy_time])->getResult();
        if(!empty($buyResult)){
            foreach ($buyResult as $item) {
                $is_valid = 0;
                if($item['is_audit'] == 1 && $item['status'] == 0){
                   $is_valid = 1;
                       $data['related_msg'] = '发布了1条求购订单';
                }elseif($item['is_audit'] == 0 && $item['status'] == 1){
                   $is_valid = 1;
                       $data['related_msg'] = '求购订单已找到';
                }
                if($is_valid == 1){
             	   $data['city'] = $item['city'];
             	   $data['user_name'] = $this->substr_cut($item['name']);
             	   $data['alter_time'] = $item['alter_time'];
             	   $score = $data['alter_time'];
             	   $this->redis->zAdd($industry . $date, $score, json_encode($data));
             	   $this->redis->expire($industry . $date,172800);
		        }
            }
        }
        //采购商/供应商订单获取
        $OrderRes = Db::query("select add_time,buyer_name,seller_id,seller_name,city FROM sb_order WHERE add_time >= ?", [(int)$last_ansy_time])->getResult();
        if(!empty($OrderRes)){
            foreach ($OrderRes as $Order) {
                $order['city'] = $Order['city'];
                $order['user_name'] = $this->substr_cut($Order['buyer_name']);
                $order['related_msg'] = '刚买入了1笔订单';
                $order['alter_time'] = $Order['add_time'];
		        $score = $order['alter_time'];
		        $this->redis->zAdd($industry . $date,$score,json_encode($order));
                $this->redis->expire($industry . $date,172800);
                $sellerRes = Db::query("select name,city FROM sb_user WHERE user_id = ?", [(int)$Order['seller_id']])->getResult();
                if(!empty($sellerRes)){
                    foreach ($sellerRes as $seller) {
                        $order['city'] = $seller['city'];
                        $order['user_name'] = $this->substr_cut($seller['name']);
                        $order['related_msg'] = '刚售出1笔订单';
			            $this->redis->zAdd($seller_event . $date,$score,json_encode($order));
                        $this->redis->expire($seller_event . $date,172800);

                    }
                }
            }
        }

        //供应商报价获取
        $offerRes = Db::query("select t.user_id,sb_buy.amount,sb_buy.unit,t.status,t.offer_time,sb_user.name,sb_user.city FROM sb_offer t LEFT JOIN sb_user ON sb_user.user_id = t.user_id LEFT JOIN sb_buy ON sb_buy.buy_id = t.buy_id WHERE offer_time >= ?", [(int)$last_ansy_time])->getResult();
        if(!empty($offerRes)){
            foreach ($offerRes as $offerRe) {
                $offer['city'] = $offerRe['city'];
                $offer['user_name'] = $this->substr_cut($offerRe['name']);
                $offer['related_msg'] = '已报价接单' . $offerRe['amount'] . $offerRe['unit'];
                $offer['alter_time'] = $offerRe['offer_time'];
		        $score = $offer['alter_time'];
                $this->redis->zAdd($seller_event . $date,$score,json_encode($offer));
                $this->redis->expire($seller_event . $date,172800);
            }
        }

        //供应商保证金动态获取
        $safeRes = Db::query("select u.city,u.name,t.user_id,t.pay_time FROM sb_safe_price AS t RIGHT JOIN sb_user AS u ON u.user_id = t.user_id WHERE t.pay_time >= ?", [(int)$last_ansy_time])->getResult();
        if(!empty($safeRes)){
            foreach ($safeRes as $safeRe) {
                $safe['city'] = $safeRe['city'];
                $safe['user_name'] = $this->substr_cut($safeRe['name']);
                $safe['related_msg'] = '缴存了保证金';
                $safe['alter_time'] = $safeRe['pay_time'];
                $score = $safe['alter_time'];
                $this->redis->zAdd($seller_event . $date,$score,json_encode($safe));
                $this->redis->expire($seller_event . $date,172800);
            }
        }

        //供应商实力商家动态获取
        $strengthRes = Db::query("select u.city,u.name,t.user_id,t.start_time FROM sb_user_strength AS t RIGHT JOIN sb_user AS u ON u.user_id = t.user_id WHERE t.start_time >= ?", [(int)$last_ansy_time])->getResult();
        if(!empty($strengthRes)){
            foreach ($strengthRes as $strengthItem) {
                $strength['city'] = $strengthItem['city'];
                $strength['user_name'] = $this->substr_cut($strengthItem['name']);
                $strength['related_msg'] = '开通了实力商家';
                $strength['alter_time'] = $strengthItem['start_time'];
                $score = $strength['alter_time'];
                $this->redis->zAdd($seller_event . $date,$score,json_encode($strength));
                $this->redis->expire($seller_event . $date,172800);
            }
        }
        $this->redis->hSet(INDUSTRY_NEWS_TIME,'industry',$date_time_format);
        return [$last_ansy_time];
    }

    /**
     * 供应商个性化标签缓存
     * @author Nihuan
     * @Scheduled(cron="0 0 06 * * *")
     */
    public function userViewBuyTagTask()
    {
        $tag_index = 'user_personal_tag:';
        $last_time = strtotime("-3 day");
//        $last_id = 0;
//        $userCount = User::count('*',[['last_time','>=',$last_time], 'status' => 1,['user_id','>',$last_id]])->getResult();
//        $pages = ceil($userCount/$limit);
//        if($pages > 0){
//            for ($i = 0; $i < $pages; $i++){
//                $user_ids = [];
                $userResult = User::findAll(
                    [['last_time','>=',$last_time], 'status' => 1],
                    ['orderBy' => ['user_id' => 'ASC'], 'fields' => ['user_id']]
                )->getResult();
                if(!empty($userResult)){
                    foreach ($userResult as $item) {
                        $black_tags = $this->redis->sMembers('@Mismatch_tag_' . $item['userId']);
                        $visit_tags = $this->buyData->getUserVisitBuyTag($item['userId'],$last_time,$black_tags);
                        if(!empty($visit_tags)){
                            $this->redis->hMset($tag_index . $item['userId'],['visit' => $visit_tags]);
                        }
                        $offer_tags = $this->buyData->getUserOfferBid($item['userId'],$last_time,$black_tags);
                        if(!empty($offer_tags)){
                            $this->redis->hMset($tag_index . $item['userId'],['offer' => $offer_tags]);
                        }
                    }
//                    $last_id = end($user_ids);
                }
//            }
//        }
        return ['个性化标签缓存'];
    }


//    /**
//     * 采购商个性化标签
//     * @throws \Swoft\Db\Exception\DbException
//     *  @Scheduled(cron="0 30 03 * * *")
//     */
//    public function userCustomerTagTask()
//    {
//        $tag_index = 'user_customer_tag:';
//        $last_time = strtotime("-60 day");
//        $userResult = User::findAll(
//            [['last_time','>=',$last_time], 'status' => 1],
//            ['orderBy' => ['user_id' => 'ASC'], 'fields' => ['user_id']]
//        )->getResult();
//        if(!empty($userResult)){
//            foreach ($userResult as $item) {
//                $custom_tag_list = [];
//                //发布采购品类
//                $tag_list = $this->buyData->getUserBuyIdsHalfYear($item['userId']);
//                if(!empty($tag_list)){
//                    foreach ($tag_list as $key => $tag) {
//                        $custom_tag_list[$key] = array_sum($tag);
//                    }
//                }
//                //搜索关键词
//                $search_list = $this->buyData->getUserSearchKeyword($item['userId']);
//                if(!empty($search_list)){
//                    foreach ($search_list as $sk => $search) {
//                        if(isset($custom_tag_list[$sk])){
//                            $custom_tag_list[$sk] += array_sum($search);
//                        }else{
//                            $custom_tag_list[$sk] = array_sum($search);
//                        }
//                    }
//                }
//                //产品品类
//                $product_list = $this->productData->getUserVisitProduct($item['userId']);
//                if(!empty($product_list)){
//                    foreach ($product_list as $pk => $pv) {
//                        if(isset($custom_tag_list[$pk])){
//                            $custom_tag_list[$pk] += array_sum($pv);
//                        }else{
//                            $custom_tag_list[$pk] = array_sum($pv);
//                        }
//                    }
//                }
//                if(!empty($custom_tag_list)){
//                    $this->redis->delete($tag_index . $item['userId']);
//                    foreach ($custom_tag_list as $ck => $cv) {
//                        $this->redis->zAdd($tag_index . $item['userId'],$cv,$ck);
//                    }
//                }
//                $tag_list = [];
//                $search_list = [];
//                $product_list = [];
//            }
//        }
//    }


    /**
     * 字符串隐藏生成
     * @param $user_name
     * @return string
     */
    protected function substr_cut($user_name){
        $strlen     = mb_strlen($user_name, 'utf-8');
        $start = 1;
        $end = 1;
        $end_start = -1;
        $firstStr     = mb_substr($user_name, 0, $start, 'utf-8');
        $lastStr     = mb_substr($user_name, $end_start, $end, 'utf-8');
        $lessStr = $strlen - $start - $end;
        $repeat_len = $lessStr > 3 ? 3 : $lessStr;
        if($repeat_len >= 1){
            $strData = $firstStr . str_repeat("*", $repeat_len) . $lastStr;
        }elseif($repeat_len == 0){
            $strData = $firstStr . '*';
        }else{
            $strData = $user_name;
        }

        return $strData;
    }
}
