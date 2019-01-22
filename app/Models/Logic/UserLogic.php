<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Logic;

use App\Models\Dao\OfferDao;
use App\Models\Data\UserData;
use Swoft\Bean\Annotation\Bean;
use Swoft\Redis\Redis;
use Swoft\Bean\Annotation\Inject;

/**
 * 用户逻辑层
 * 同时可以被controller server task使用
 *
 * @Bean()
 * @uses      UserLogic
 */
class UserLogic
{
    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @Inject()
     * @var OfferDao
     */
    private $offerDao;

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @param array $params
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function checkUserTagExists(array $params)
    {
        $is_meet = $this->userData->isUserTagInActivity($params['user_id'],$params['tag_id']);
        return $is_meet;
    }

    /**
     * @param $params
     * @param $last_day_time
     * @param $day_type
     * @throws \Swoft\Db\Exception\DbException
     */
    public function supplierDataList($params, $last_day_time, $day_type)
    {
        $limit = 500;
        $day_list = [];
        for ($i = 1;$i<=$day_type;$i++){
            $day_list[] = date('Y-m-d',strtotime("-{$i} day"));
        }
        $user_list = $this->userData->getUserDataByParams($params, $last_day_time);
        if(!empty($user_list)){
            $user_count = count($user_list);
            $pages = ceil($user_count/$limit);
            if($pages >= 0){
                for ($i = 0;$i < $pages; $i++){
                    $supplierAll = [];
                    $list = array_slice($user_list,$i,$limit);
                    foreach ($list as $item) {
                        $user_id = $item['userId'];
                        $data['user_id'] = $user_id;
                        $data['days_type'] = $day_type;
                        //登录天数获取
                        $login_days = $this->userData->getUserLoginTimes($user_id, $last_day_time);
                        $data['login_days'] = count($login_days);
                        //未读采购数获取
                        $unread_count = 0;
                        if(!empty($login_days)){
                            $unread_list = array_diff($day_list, $login_days);
                            $unread_count = $this->userData->getUserSubscriptBuyCount($user_id,$unread_list);
                        }
                        $data['unread_count'] = (int)$unread_count;
                        //消息回复情况
                        $data['avg_reply_sec'] = 0;
                        $data['un_reply_count'] = 0;
                        $userDialog = $this->userData->getUserChatData($user_id,$last_day_time);
                        if(!empty($userDialog)){
                            $data['avg_reply_sec'] = $userDialog['avg_chat_duration'];
                            $data['un_reply_count'] = $userDialog['un_reply_count'];
                        }
                        //访客数据情况
                        $userVisit = $this->userData->getUserVisitData($user_id,$last_day_time);
                        if(!empty($userVisit)){
                            $data['total_visit_count'] = $userVisit['count'];
                            $data['un_reply_visit'] = (int)$userVisit['un_chat_count'];
                        }
                        $data['record_time'] = time();
                        $supplierAll[] = $data;
                    }
                    $this->userData->saveSupplierData($supplierAll);
                }
            }
        }
    }

    /**
     * 获取满足条件的用户信息
     * @param array $user_list
     * @param string $tag
     * @param int $offer_count
     * @param int $last_time
     * @return array
     */
    public function getRecommendUserList(array $user_list, string $tag, int $offer_count, int $last_time)
    {
        $cache_keys = 'recommend_shop_key_' . md5($tag);
        if($this->redis->exists($cache_keys)){
            $this->redis->delete($cache_keys);
        }
        $match_user_ids = [];
        $match_tag_user_ids = $this->userData->getUserByTag($user_list,$tag);
        if(!empty($match_tag_user_ids)){
            foreach ($match_tag_user_ids as $key => $matched) {
                //获取最近30天报价数
                $offerParams = [
                    'offerer_id' => $matched['user_id'],
                    'offer_time' => ['>',$last_time]
                ];
                $match_offer_count = $this->offerDao->getUserOfferCount($offerParams);
                if($match_offer_count >= $offer_count){
                    $cache_value = $matched['user_id'] . '#' . $matched['top_name'];
                    $this->redis->sAdd($cache_keys,$cache_value);
                    $match_user_ids[] = $matched['user_id'];
                }
            }
        }
        if($this->redis->exists($cache_keys)){
            $this->redis->expire($cache_keys,86200);
        }
        return $match_user_ids;
    }

    /**
     * 实商数据
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getStrengthUserList()
    {
        return $this->userData->getStrengthList();
    }

    /**
     * 供应商推荐
     * @param array $params
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getRecommendShopList(array $params)
    {
        $shop_info = [];
        $tag_list = $params['tag_list'];
        $match_list = [];
        if(!empty($tag_list)){
            foreach ($tag_list as $tag) {
                $cache_key = 'recommend_shop_key_' . md5($tag);
                if($this->redis->exists($cache_key)){
                    $tag_user = $this->redis->SRANDMEMBER($cache_key,3);
                    if(count($tag_user) > 0){
                        $match_list[] = $tag_user;
                    }
                }
            }
        }

        $match_record_list = [];
        if(!empty($match_list)){
            for ($i = 1; $i <= 3;){
                $total_count = 0;
                foreach ($match_list as $key => $item) {
                    $current_item = array_splice($item,0,1);
                    if(!empty($current_item)){
                        $shop = explode('#',$current_item[0]);
                        if(isset($match_record_list[$shop[0]])){
                            continue;
                        }
                        $match_record_list[$shop[0]] = $shop[1];
                        $i ++;
                    }else{
                        array_splice($match_list,$key,1);
                        $i ++;
                    }
                    $match_list[$key] = $item;
                    $total_count += count($item);
                }
                if(count($match_list) == 0 || $total_count == 0){
                    break;
                }
            }
        }
        if(!empty($match_record_list)){
            $user_ids = array_keys($match_record_list);
            $fields = ['u.user_id','u.name','u.portrait','u.level','u.role','u.certification_type','u.safe_price','t.level as deposit_level'];
            $shop_info = $this->userData->getStrengthList(['user_ids' => $user_ids],$fields);
            if(!empty($shop_info)){
                foreach ($shop_info as $key => $user) {
                    $shop_info[$key]['match_tag_desc'] = $match_record_list[$user['user_id']] . '优质供应商';
                    $shop_info[$key]['deposit'] = 1;
                    $shop_info[$key]['deposit_type'] = (int)$user['deposit_level']/5;
                    unset($shop_info[$key]['deposit_level']);
                }
            }
        }
        return $shop_info;
    }
}