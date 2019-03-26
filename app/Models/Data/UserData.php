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
use App\Models\Dao\UserStrengthDao;
use App\Models\Dao\BuyRelationTagDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;

/**
 * 用户数据类
 * @Bean()
 * @uses      UserData
 * @author    Nihuan
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
     * @var UserStrengthDao
     */
    private $userStrengthDao;

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
     * @author yang
     * @param $params
     * @param $user_id
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function userUpdate(array $params, int $user_id)
    {
        return $this->userDao->userUpdate($params,$user_id);
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
     * @throws \Swoft\Db\Exception\DbException
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
            $chat_duration = current($chatInfo);
            $chat_info['avg_chat_duration'] = isset($chat_duration['avg_chat_duration']) ? (int)$chat_duration['avg_chat_duration'] : 0;
            $chat_info['un_reply_count'] = isset($chat_duration['un_reply_count']) ? (int)$chat_duration['un_reply_count'] : 0;
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
        $visit_chat_count = 0;
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
                $visit_chat_count = count($visit_chat);
            }
        }
        $visit['count'] = count($visit_list);
        $visit['un_chat_count'] = $visit['count'] - $visit_chat_count;
        return $visit;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function saveSupplierData($data)
    {
        return $this->userDao->saveSupplierData($data);
    }

    /**
     * 发送通知成功修改
     * @param $data
     * @return mixed
     */
    public function updateSupplierData($where)
    {
        $data = [
            'send_time'=>time(),
            'send_status'=>1
            ];
        return $this->userDao->updateSupplierData($data,$where);
    }

    /**
     * 不需要发送通知修改
     * @param $data
     * @return mixed
     */
    public function updateStatusSupplierData($where)
    {
        $data = ['send_status'=>-1];
        return $this->userDao->updateSupplierData($data,$where);
    }

    /**
     * @param $condition
     * @param int $limit
     * @return mixed
     */
    public function getSupplierData($condition,$limit = 500)
    {
        return $this->userDao->getSupplierData($condition,$limit);
    }

    /**
     * @param $condition
     * @return mixed
     */
    public function getSupplierCount($condition)
    {
        return $this->userDao->getSupplierCount($condition);
    }

    /**
     * @param int $user_id
     * @return mixed
     */
    public function getUserStrengthInfo(int $user_id)
    {
        return $this->userDao->getUserStrengthInfo($user_id);
    }

    /**
     * @param int $pay_time
     * @param int $type
     * @return mixed
     */
    public function getStrengthActivity(int $pay_time, int $type)
    {
        return $this->userDao->getStrengthActivity($pay_time, $type);
    }

    /**
     * @param int $user_id
     * @param int $id
     * @param array $params
     * @return \Swoft\Core\ResultInterface
     */
    public function userStrengthPlus(int $user_id, int $id, array $params)
    {
        return $this->userDao->userStrengthPlus($user_id, $id, $params);
    }

    /**
     * @param int $user_id
     * @param string $order_num
     * @param $total_amount
     * @param $take_time
     * @param $strength_amount
     * @return mixed
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function saveStrengthOrder(int $user_id, string $order_num, $total_amount, $take_time, $strength_amount)
    {
        return $this->userDao->strengthOrderRecord($user_id, $order_num, $total_amount, $take_time, $strength_amount);
    }

    /**
     * @param int $user_id
     * @param string $order_num
     * @return \Swoft\Core\ResultInterface
     */
    public function checkStrengthOrderRecord(int $user_id, string $order_num)
    {
        return $this->userDao->checkStrengthOrderRecord($order_num,$user_id);
    }

    /**
     * @param int $type
     * @return array
     */
    public function getAgentUser(int $type)
    {
        return $this->userDao->getAgentInfo($type);
    }

    /**
     * 是否是实商
     * @author yang
     * @param int $user_id
     * @return bool
     */
    public function getIsUserStrength(int $user_id)
    {
        $time = time();
        $where = [
            'user_id' => $user_id,
            ['start_time', '<', $time],
            ['end_time', '>', $time],
            'is_expire' => 0,
        ];
        $reslut = $this->userStrengthDao->getStrengInfoOne($where, ['id']);
        if (isset($reslut['id'])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 是否是实商
     * @author yang
     * @param string $name
     * @return array
     */
    public function getUserGrowthRule(string $name)
    {
        return $this->userDao->userGrowthRule($name);
    }

    /**
     * 添加成长值记录
     * @author yang
     * @param string $params
     * @return bool
     */
    public function userGrowthRecordInsert(array $params)
    {
        return $this->userDao->UserGrowthRecordInsert($params);
    }

    /**
     * 查询成长值记录
     * @author yang
     * @param string $params
     * @return bool
     */
    public function userGrowthRecordOne(int $user_id, string $name)
    {
        return $this->userDao->UserGrowthRecordOne($user_id, $name);
    }

    /**
     * 更新成长值记录
     * @author yang
     * @param array $params
     * @return bool
     */
    public function userGrowthRecordUpdate(array $params, int $user_id, string $name)
    {
        $params['update_time'] = time();
        return $this->userDao->userGrowthRecordUpdate($params, $user_id, $name);
    }

    /**
     * 获取用户积分对应的等级
     * @author yang
     * @param string $growth
     * @return array
     */
    public function getUserLevelRule(int $growth)
    {
        return $this->userDao->getUserLevelRule($growth);
    }

    /**
     * 更新成长值
     * @author yang
     * @param int $params
     * @param int $user_id
     * @return bool
     */
    public function userGrowthUpdate(int $growth, int $user_id, int $is_add = 0)
    {
        $params = [
            'growth' => $growth,
            'update_time' => time(),
        ];
        if ($is_add==0){
            $growth_info = $this->userDao->UserGrowth($user_id);
            $growth_num = $growth_info['growth'] + $growth;
            $params = [
                'growth' => $growth_num,
                'update_time' => time(),
            ];
        }

        return $this->userDao->UserGrowthUpdate($params, $user_id);
    }

    /**
     * 获取用户列表
     * @author yang
     * @return array
     */
    public function getUserList(int $post_user_id, int $limit)
    {
        return $this->userDao->getUserList($post_user_id, $limit);
    }

    /**
     * 获取用户评价数
     * @author yang
     * @return array
     */
    public function getReviewCount($user_id)
    {
        return $this->userDao->getReviewCount($user_id);
    }

    /**
     * 获取卖家好评数
     * @author yang
     * @return array
     */
    public function getReviewGoodCount($user_id)
    {
        return $this->userDao->getReviewGoodCount($user_id);
    }

    /**
     * 获取卖家差评数
     * @author yang
     * @return array
     */
    public function getReviewBadCount($user_id)
    {
        return $this->userDao->getReviewBadCount($user_id);
    }

    /**
     * 获取采购身份
     * @author yang
     * @return int
     */
    public function getUserPurchaserRole($user_id)
    {
        return $this->userDao->getUserPurchaserRole($user_id);
    }

    /**
     * 获取主营行业
     * @author yang
     * @return int
     */
    public function getUserPurchaserIndustry($user_id)
    {
        return $this->userDao->getUserPurchaserIndustry($user_id);
    }

    /**
     * 获取个人资料完善度
     * @author yang
     * @return int
     */
    public function getUserDateInfo($user_id)
    {
        $user_data_growth = 0;
        $user_info = $this->getUserInfo($user_id);
        //计算资料完善度
        if (!empty($user_info['main_product'])){
            $user_data_growth += 33;//主营产品
        }
        $purchaser_role = $this->getUserPurchaserRole($user_id);//采购身份
        if (!empty($purchaser_role)){
            $user_data_growth += 34;
        }
        $purchaser_industry = $this->getUserPurchaserIndustry($user_id);//主营行业
        if (!empty($purchaser_industry)){
            $user_data_growth += 33;
        }

        return $user_data_growth;
    }

    /**
     * 获取用户成长值
     * @author yang
     * @return int
     */
    public function userGrowth($user_id)
    {
        $result = $this->userDao->UserGrowth($user_id);
        if (isset($result)){
            return $result['growth'];
        }else{
            return 0;
        }
    }

    /**
     * @param array $params
     * @param array $field
     * @return mixed
     */
    public function getWillExpStrength(array $params,array $field)
    {
        return $this->userStrengthDao->getStrengInfoAll($params,$field);
    }
}
