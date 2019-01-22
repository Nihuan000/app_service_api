<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Data;

use App\Models\Dao\UserDao;
use App\Models\Dao\BuyRelationTagDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;

/**
 * 用户数据类
 * @Bean()
 * @uses      UserData
 * @author    Nihuan
 * @method saveSupplierData(array $supplierAll)
 */
class UserData
{
    /**
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * @Inject()
     * @var BuyRelationTagDao
     */
    private $buyRelDao;


    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @author Nihuan
     * @param $uid
     * @return mixed
     */
    public function getUserInfo($uid)
    {
        return $this->userDao->getUserInfoByUid($uid);
    }


    /**
     * 根据id列表获取用户信息
     * @author Nihuan
     * @param $user_ids
     * @param array $fields
     * @return mixed
     */
    public function getUserByUids($user_ids, $fields = ['*'])
    {
        return $this->userDao->getInfoByUids($user_ids, $fields);
    }

    /**
     * author: nihuan
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getTesters()
    {
        $is_delete = 0;
        $type = 5;
        return $this->userDao->getTestersInfo($is_delete, $type);
    }

    /**
     * 配置获取
     * @param $keyword
     * @return array|bool|int|string|null
     */
    public function getSetting($keyword)
    {
        $setting_info = $this->userDao->getSettingInfo($keyword);
        $setting = current($setting_info);
        if(!empty($setting)){
            switch ($setting['value_type']){
                case 1:
                    $value = (string)$setting['value'];
                    break;

                case 2:
                    $value = (int)$setting['value'];
                    break;

                case 3:
                    $value = [];
                    if(!empty($setting['value'])){
                        $value = explode(',',$setting['value']);
                    }
                    break;

                case 4:
                    $value = (bool)$setting['value'];
                    break;


                default:
                    $value = (string)$setting['value'];
            }

            return $value;
        }else{
            return null;
        }
    }

    /**
     * @param $user_id
     * @param $tag_id
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function isUserTagInActivity($user_id, $tag_id)
    {
        $tag_meet_at = 0;
        $cache_index = 'purchase_goods_index:';
        if($this->redis->sIsmember($cache_index . $tag_id,$user_id)){
            return $tag_meet_at = 1;
        }
        $user_tag_string = $this->redis->get('user_subscription_tag:' . $user_id);
        $user_tag_list = json_decode($user_tag_string,true);
        if(empty($user_tag_list)){
            $user_tag_list = $this->userDao->getUserTagByUid($user_id);
            if(!empty($user_tag_list)){
                $this->redis->set('user_subscription_tag:' . $user_id,json_encode($user_tag_list));
            }
        }
        if(!empty($user_tag_list)){
            foreach ($user_tag_list as $item) {
                if($tag_id == $item['top_id']){
                    $this->redis->sAdd($cache_index . $item['top_id'],$user_id);
                    $tag_meet_at = 1;
                }
            }
        }
        return $tag_meet_at;
    }

    /**
     * 实商列表
     * @param array $params
     * @param array $field
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getStrengthList(array $params = [], array $field = [])
    {
        return $this->userDao->getUserStrengthList($params, $field);
    }

    /**
     * @param array $user_id
     * @param string $tag
     * @return mixed
     */
    public function getUserByTag(array $user_id, string $tag)
    {
        return $this->userDao->getUserListBySecTag($user_id,$tag);
    }

    /**
     * 获取用户数
     * @param array $params
     * @return mixed
     */
    public function getUserCountByParams(array $params)
    {
        return $this->userDao->getUserCountByParams($params);
    }

    /**
     * 用户数据列表
     * @param array $params
     * @param int $limit
     * @return array
     */
    public function getUserDataByParams(array $params, int $limit)
    {
        $fields = ['user_id'];
        $user_list = $this->userDao->getUserListByParams($params,$fields,$limit);
        return $user_list;
    }

    /**
     * 用户登录次数
     * @param int $user_id
     * @param int $last_day_time
     * @return array
     */
    public function getUserLoginTimes(int $user_id, int $last_day_time)
    {
        $login_days = [];
        $login_list = $this->userDao->getUserLoginDays($user_id, $last_day_time);
        foreach ($login_list as $item) {
            $login_days[] = $item['addtime'];
        }
        return $login_days;
    }

    /**
     * @param int $user_id
     * @param array $days
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserSubscriptBuyCount(int $user_id, array $days)
    {
        return $this->buyRelDao->getUserSubscriptBuy($user_id, $days);
    }

    /**
     * 用户回复数据
     * @param int $user_id
     * @param int $last_day_time
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserChatData(int $user_id, int $last_day_time)
    {
        $chat_info = [];
        $chatInfo = $this->userDao->getUserChatDuration($user_id,$last_day_time);
        if(!empty($chatInfo)){
            $chat_info['avg_chat_duration'] = (int)$chatInfo['avg_chat_duration'];
            $chat_info['un_reply_count'] = (int)$chatInfo['un_reply_count'];
        }
        return $chat_info;
    }

    /**
     * 用户访客数据
     * @param int $user_id
     * @param int $last_day_time
     * @return array
     */
    public function getUserVisitData(int $user_id, int $last_day_time)
    {
        $visit_list = [];
        $visitInfo = $this->userDao->getUserVisitData($user_id, $last_day_time);
        if(!empty($visitInfo)){
            foreach ($visitInfo as $item) {
                $visit_list[] = $item['user_id'];
            }
        }
        $visit_chat = 0;
        if(!empty($visit_list)){
            $chat_user_list = [];
            $chat_list = $this->userDao->getUserChatStatisitcs($user_id,$last_day_time);
            if(!empty($chat_list)){
                foreach ($chat_list as $item) {
                    if($item['from_id'] != $user_id){
                        $chat_user_list[] = $item['from_id'];
                    }
                    if($item['target_id'] != $user_id){
                        $chat_user_list[] = $item['target_id'];
                    }
                }
                array_unique($chat_user_list);
                $visit_chat = array_intersect($visit_list,$chat_user_list);
            }
        }
        $visit['count'] = count($visit_list);
        $visit['un_chat_count'] = $visit['count'] - $visit_chat;
        return $visit;
    }
}
