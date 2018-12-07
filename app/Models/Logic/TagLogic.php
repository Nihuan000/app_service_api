<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-21
 * Time: 下午5:43
 */

namespace App\Models\Logic;


use App\Models\Dao\BuyDao;
use App\Models\Dao\UserDao;
use App\Models\Data\BuyData;
use App\Models\Data\ProductData;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Bean;
use Swoft\Redis\Redis;

/**
 * 标签逻辑层
 * @Bean()
 * @uses  TagLogic
 * @author Nihuan
 * @package App\Models\Logic
 */
class TagLogic
{

    /**
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $searchRedis;

    /**
     * @Inject()
     * @var BuyDao
     */
    private $buyDao;

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
     * @param array $event
     * @throws \Swoft\Db\Exception\DbException
     */
    public function event_analysis(array $event)
    {
        $user_tag_list = $this->userDao->getUserTagByUid($event['user_id']);
        if(!empty($user_tag_list)){
            $this->redis->set('user_subscription_tag:' . $event['user_id'],json_encode($user_tag_list));
        }
    }

    /**
     * @param int $user_id
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function new_reg_recommend(int $user_id)
    {
        $msg_count = 0;
        $now_date = date('Y-m-d');
        $recommend_key = '@RecommendMsgQueue_' .$now_date;
        $user_tag_list = $this->userDao->getUserTagByUid($user_id);
        $subscription_tag = [];
        if(!empty($user_tag_list)){
            foreach ($user_tag_list as $item) {
                $subscription_tag[] = $item['tag_id'];
            }
            $tag_list = array_slice($subscription_tag,0,5);
            if(!empty($tag_list)){
                $buy_ids = [];
                foreach ($tag_list as $tag) {
                    $buy_info = $this->buyDao->getBuyInfoByTagId($tag,$buy_ids);
                    if(!empty($buy_info)){
                        $match_buy = current($buy_info);
                        if(!empty($match_buy)){
                            $this->searchRedis->lPush($recommend_key,$user_id . '#' . $match_buy['buy_id']);
                            $buy_ids[] = $match_buy['buy_id'];
                            $msg_count += 1;
                        }
                    }
                }
            }
        }
        return $msg_count;
    }

    /**
     * 刷新个性化标签
     * @param int $user_id
     * @throws \Swoft\Db\Exception\DbException
     */
    public function refresh_tag(int $user_id)
    {
        $tag_index = 'user_customer_tag:';
        $custom_tag_list = [];
        //发布采购品类
        $tag_list = $this->buyData->getUserBuyIdsHalfYear($user_id);
        if(!empty($tag_list)){
            foreach ($tag_list as $key => $tag) {
                $custom_tag_list[$key] = array_sum($tag);
            }
        }
        //搜索关键词
        $search_list = $this->buyData->getUserSearchKeyword($user_id);
        if(!empty($search_list)){
            foreach ($search_list as $sk => $search) {
                if(isset($custom_tag_list[$sk])){
                    $custom_tag_list[$sk] += array_sum($search);
                }else{
                    $custom_tag_list[$sk] = array_sum($search);
                }
            }
        }
        //产品品类
        $product_list = $this->productData->getUserVisitProduct($user_id);
        if(!empty($product_list)){
            foreach ($product_list as $pk => $pv) {
                if(isset($custom_tag_list[$pk])){
                    $custom_tag_list[$pk] += array_sum($pv);
                }else{
                    $custom_tag_list[$pk] = array_sum($pv);
                }
            }
        }
        if(!empty($custom_tag_list)){
            $this->redis->delete($tag_index . $user_id);
            foreach ($custom_tag_list as $ck => $cv) {
                $this->redis->zAdd($tag_index . $user_id,$cv,$ck);
            }
        }
    }
}