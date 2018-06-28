<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-26
 * Time: 下午6:19
 * Desc: 定时任务
 */

namespace App\Tasks;

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
                $sellerRes = Db::query("select name,city FROM sb_user WHERE user_id = ?", [(int)$Order['seller_id']]);
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


//        /**
//     * 用户产品标签生成
//     * @Author Nihuan
//     * @Version 1.0
//     * @Date 18-01-23
//     */
//    public function buyTagGenealTask(){
//        $user_tag_list = [];
//        $last_time = strtotime('-1 month');
//        $buyCount = $this->mysql_pool->dbQueryBuilder->coroutineSend(null,"SELECT count(*) AS count FROM sb_buy WHERE alter_time >= {$last_time}");
//        $count = yield $buyCount;
//        if($count['result'][0]['count'] > 0){
//            $last_id = 0;
//            $pages = ceil($count['result'][0]['count']/$this->limit);
//            if($pages > 0){
//                for ($i=0;$i<=$pages;$i++){
//                    $buySql = $this->mysql_pool->dbQueryBuilder->coroutineSend(null,"select b.buy_id,b.user_id,l.name as tag_name FROM sb_buy b LEFT JOIN sb_buy_tag as bt ON bt.public_id = b.buy_id LEFT JOIN sb_label AS l ON l.lid = bt.label_id WHERE b.buy_id > {$last_id} AND b.alter_time >= {$last_time} AND bt.category_id = 1 ORDER BY b.buy_id ASC LIMIT {$this->limit}");
//
//                    $buyRes = yield $buySql;
//                    if(!empty($buyRes['result'])){
//                        foreach ($buyRes['result'] as $val){
//                            $user_tag_list[$val['user_id']][] = $val['tag_name'];
//                            $last_id = $val['buy_id'];
//                        }
//                    }
//                }
//            }
//
//            if(!empty($user_tag_list)){
//                foreach ($user_tag_list as $key => $value){
//                    $tags = array_unique($value);
//                    $tag_list = implode(',',$tags);
//                    $tagData = $this->redis_pool->getCoroutine()->hSet(USER_TAG_LIST,$key,$tag_list);
//                    yield $tagData;
//                }
//            }
//        }
//    }
//
//
//    /**
//     * 用户产品标签生成
//     * @Author Nihuan
//     * @Version 1.0
//     * @Date 18-01-23
//     */
//    public function MatchProductTask()
//    {
//        $redisCoroutine = $this->redis_pool->getCoroutine()->hKeys(USER_TAG_LIST);
//        $allCache = yield $redisCoroutine;
//        if(!empty($allCache)){
//            foreach ($allCache as $item) {
//                $user_id = $item;
//                $user_match_product = 'match_product:' . $user_id;
//                $tags = yield $this->redis_pool->getCoroutine()->hGet(USER_TAG_LIST,$user_id);
//                $searchService = $this->loader->model('app/Models/SearchModel',$this);
//                $params = [
//                    'keyword' => $tags,
//                    'pageSize' => 50
//                ];
//                $product_list = yield $searchService->search_service('product',$params,(int)$user_id);
//                if(!empty($product_list)){
//                    yield $this->redis_pool->getCoroutine()->set($user_match_product,json_encode($product_list));
//                }
//            }
//        }
//    }
//
//
//    /**
//     * 用户店铺标签生成
//     * @Author Nihuan
//     * @Version 1.0
//     * @Date 18-01-23
//     */
//    public function MatchShopTask()
//    {
//        $redisCoroutine = $this->redis_pool->getCoroutine()->hKeys(USER_TAG_LIST);
//        $allCache = yield $redisCoroutine;
//        if(!empty($allCache)) {
//            foreach ($allCache as $item) {
//                $user_id = $item;
//                $tags = yield $this->redis_pool->getCoroutine()->hGet(USER_TAG_LIST,$user_id);
//                $user_match_shop = 'match_shop:' . $user_id;
//                $params = [
//                    'keyword' => $tags,
//                    'pageSize' => 30
//                ];
//                $searchService = $this->loader->model('app/Models/SearchModel',$this);
//                $shop_object_list = yield $searchService->search_service('shop',$params,(int)$user_id);
//                if(!empty($shop_object_list)){
//                    $user_ids = array_column($shop_object_list,'user_id');
//                    $user_list = implode(',',$user_ids);
//                    $Query = $this->mysql_pool->dbQueryBuilder->coroutineSend(null,"SELECT user_id,cover FROM sb_product WHERE user_id IN ({$user_list}) AND del_status = 1 GROUP BY user_id ORDER BY pro_id DESC");
//                    $shop_product_list = yield $Query;
//                    $this->log(json_encode($shop_product_list));
//                    if(!empty($shop_product_list['result'])){
//                        foreach ($shop_product_list['result'] as $product) {
//                            $shop_object_list[$product['user_id']]['cover'] = get_all_pic_url($product['cover']);
//                        }
//                    }
//                }
//                foreach ($shop_object_list as $shop) {
//                    $shop_list[] = $shop;
//                }
//                if(!empty($shop_list)){
//                    yield $this->redis_pool->getCoroutine()->set($user_match_shop,json_encode($shop_list));
//                }
//            }
//        }
//    }
//
//
//
//       /**
//     * 相似推荐
//     * @Author Nihuan
//     * @Version 1.0
//     * @Date 18-01-23
//     */
//    public function SimilarBuyTask(){
//        $sellerCache = 'similar_buy:';
//        $smart_buy = 'similar_buy_list';
//        $user_buy_list = [];
//        $last_time = strtotime('-7 day');
//        $buyCount = $this->mysql_pool->dbQueryBuilder->coroutineSend(null,
//            "SELECT count(*) AS count FROM sb_buy WHERE alter_time >= {$last_time} AND status = 0");
//        $count = yield $buyCount;
//        if ($count['result'][0]['count'] > 0) {
//            $last_id = 0;
//            $pages = ceil($count['result'][0]['count'] / $this->limit);
//            if ($pages > 0) {
//                for ($i = 0; $i <= $pages; $i++) {
//                    $buySql = $this->mysql_pool->dbQueryBuilder->coroutineSend(null,
//                        "SELECT buy_id,pic,remark FROM sb_buy WHERE buy_id > {$last_id} AND alter_time >= {$last_time} AND status = 0 ORDER BY buy_id ASC LIMIT {$this->limit}");
//                    $buyRes = yield $buySql;
//                    if (!empty($buyRes['result'])) {
//                        foreach ($buyRes['result'] as $buy) {
//                            $checkRes = yield $this->redis_pool->getCoroutine()->sIsMember($smart_buy,$buy['buy_id']);
//                            if($checkRes == 0){
//                                $tagSql = $this->mysql_pool->dbQueryBuilder->coroutineSend(null,
//                                    "select l.label_name as tag_name,l.type FROM sb_buy_front_label_relation bt LEFT JOIN sb_buy_front_label AS l ON l.label_id = bt.label_id WHERE bt.buy_id = {$buy['buy_id']}");
//                                $tagRes = yield $tagSql;
//                                $malong_tag = [];
//                                if (!empty($tagRes['result'])) {
//                                    foreach ($tagRes['result'] as $val) {
//                                        switch ($val['type']){
//                                            case 1:
//                                                $type = $this->relation_type($val['tag_name']);
//                                                $malong_tag[] = 'ProductType_' . $type;
//                                                break;
//
//                                            case 2:
//                                                $malong_tag[] = 'proname_' . $val['tag_name'];
//                                                break;
//
//                                            case 3:
//                                                $malong_tag[] = 'ingredient_' . $val['tag_name'];
//                                                break;
//
//                                            case 4:
//                                                $malong_tag[] = 'crafts_' . $val['tag_name'];
//                                                break;
//
//                                            case 5:
//                                                $malong_tag[] = 'uses_' . $val['tag_name'];
//                                                break;
//                                        }
//                                    }
//                                }
//                                if(!empty($buy['pic'])){
//                                    $buy_cover = get_all_pic_url($buy['pic']);
//                                    require_once MYROOT . '/src/app/ThirdParty/Malong/MalongClient.php';
//                                    $productAi = new MalongClient(PRODUCT_AI_ACCESS_ID,PRODUCT_AI_SECRET_KEY,PRODUCT_AI_LANGUAGE);
//                                    $productAiRes = $productAi->smartImage($buy_cover,$malong_tag,30);
//                                    if(!empty($productAiRes)){
//                                        $pro_ids = [];
//                                        foreach ($productAiRes as $item) {
//                                            $pro_ids[] = $item['metadata'];
//                                        }
//                                        $pro_id = implode(',',$pro_ids);
//                                        if(!empty($pro_id)){
//                                            $proInfo = yield $this->mysql_pool->dbQueryBuilder->coroutineSend(null,"SELECT user_id FROM sb_product WHERE pro_id IN ({$pro_id})");
//                                            if(!empty($proInfo['result'])){
//                                                foreach ($proInfo['result'] as $pro) {
//                                                    $user_buy_list[$pro['user_id']][] = [
//                                                        'buy_id' => $buy['buy_id'],
//                                                        'pic' => $buy_cover,
//                                                        'remark' => $buy['remark']
//                                                    ];
//                                                }
//                                            }
//                                        }
//                                    }
//                                }
//                                yield $this->redis_pool->getCoroutine()->sAdd($smart_buy,$last_id);
//                            }
//                            $last_id = $buy['buy_id'];
//                        }
//                    }
//                }
//            }
//        }
//        if(!empty($user_buy_list)){
//            foreach ($user_buy_list as $key => $item) {
//                yield $this->redis_pool->getCoroutine()->set($sellerCache . $key,json_encode($item));
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
