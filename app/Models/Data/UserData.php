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
use Swoft\Core\ResultInterface;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
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
     * @throws DbException
     */
    public function getTesters()
    {
        $is_delete = 0;
        $type = 5;
        return $this->userDao->getTestersInfo($is_delete, $type);
    }

    /**
     * @author yang
     * @param array $params
     * @param int $user_id
     * @return mixed
     * @throws DbException
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
     * @throws DbException
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
     * @throws DbException
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
     * @throws DbException
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
     * @throws DbException
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
     * @throws DbException
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
     * @param $where
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
     * @param $where
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
     * @return ResultInterface
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
     * @throws MysqlException
     */
    public function saveStrengthOrder(int $user_id, string $order_num, $total_amount, $take_time, $strength_amount)
    {
        return $this->userDao->strengthOrderRecord($user_id, $order_num, $total_amount, $take_time, $strength_amount);
    }

    /**
     * @param int $user_id
     * @param string $order_num
     * @return ResultInterface
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
     * @param array $params
     * @return mixed
     */
    public function userGrowthRecordInsert(array $params)
    {
        return $this->userDao->UserGrowthRecordInsert($params);
    }

    /**
     * 查询成长值记录
     * @author yang
     * @param int $user_id
     * @param string $name
     * @return array
     */
    public function userGrowthRecordOne(int $user_id, string $name)
    {
        return $this->userDao->UserGrowthRecordOne($user_id, $name);
    }

    /**
     * 更新成长值记录
     * @author yang
     * @param array $params
     * @param int $user_id
     * @param string $name
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
     * @param int $growth
     * @return array
     */
    public function getUserLevelRule(int $growth)
    {
        return $this->userDao->getUserLevelRule($growth);
    }

    /**
     * 更新成长值
     * @author yang
     * @param int $growth
     * @param int $user_id
     * @param int $is_add
     * @return mixed
     */
    public function userGrowthUpdate(int $growth, int $user_id, int $is_add = 0)
    {
        $params = [
            'growth' => $growth,
            'update_time' => time(),
        ];
        if ($is_add==0){
            $growth_info = $this->userDao->UserGrowth($user_id);
            if (isset($growth_info)){
                $growth_num = $growth_info['growth'] + $growth;
            }else{
                $this->userDao->UserGrowthAdd($user_id);
                $growth_num = $growth;
            }
            
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
     * @param int $post_user_id
     * @param int $limit
     * @return array
     */
    public function getUserList(int $post_user_id, int $limit)
    {
        return $this->userDao->getUserList($post_user_id, $limit);
    }

    /**
     * 获取用户评价数
     * @author yang
     * @param  int $user_id
     * @return int
     */
    public function getReviewCount($user_id)
    {
        return $this->userDao->getReviewCount($user_id);
    }

    /**
     * 获取卖家好评数
     * @param $user_id
     * @return int
     * @throws DbException
     * @author yang
     */
    public function getReviewGoodCount($user_id)
    {
        return $this->userDao->getReviewGoodCount($user_id);
    }

    /**
     * 获取卖家差评数
     * @param $user_id
     * @return int
     * @throws DbException
     * @author yang
     */
    public function getReviewBadCount($user_id)
    {
        return $this->userDao->getReviewBadCount($user_id);
    }

    /**
     * 获取采购身份
     * @param $user_id
     * @return array
     * @throws DbException
     * @author yang
     */
    public function getUserPurchaserRole($user_id)
    {
        return $this->userDao->getUserPurchaserRole($user_id);
    }

    /**
     * 获取主营行业
     * @param $user_id
     * @return array
     * @throws DbException
     * @author yang
     */
    public function getUserPurchaserIndustry($user_id)
    {
        return $this->userDao->getUserPurchaserIndustry($user_id);
    }

    /**
     * 采购身份背景表
     * @author yang
     * @param $user_id
     * @param $role_type
     * @return array
     */
    public function getUserPurchaserRoleBackground($user_id,$role_type)
    {
        return $this->userDao->getUserPurchaserRoleBackground($user_id,$role_type);
    }

    /**
     * 获取环境图
     * @author yang
     * @param $user_id
     * @param $type
     * @return array
     */
    public function getUserPurchaserRoleWorkImg($user_id,$type)
    {
        return $this->userDao->getUserPurchaserRoleWorkImg($user_id,$type);
    }

    /**
     * 获取品牌网站
     * @author yang
     * @param $user_id
     * @return array
     */
    public function getUserAttribute($user_id)
    {
        return $this->userDao->getUserAttribute($user_id);
    }

    /**
     * 网店地址
     * @author yang
     * @param $user_id
     * @return array
     */
    public function getUserPurchaserRoleWebsiteUrl($user_id)
    {
        $result = $this->userDao->getUserPurchaserRoleWebsiteUrl($user_id);
        if (empty($result)){
            return [];
        }else{
            return $result;
        }
    }

    /**
     * 安卓资料完善率
     * @param $user_id
     * @param $main_product
     * @return int
     * @throws DbException
     */
    public function androidUserDate($user_id,$main_product){
        $user_data_growth = 0;
        $purchaser_role = $this->userDao->getUserPurchaserRole($user_id);//采购身份
        if (!empty($purchaser_role)){
            $user_data_growth += 20;
            if (!empty($main_product)){
                $user_data_growth += 20;//主营产品
            }
        }
        $purchaser_industry = $this->getUserPurchaserIndustry($user_id);//主营行业;
        if (!empty($purchaser_industry)){
            $user_data_growth += 20;
        }

        $content = $this->userDao->getUserCompany($user_id);
        if (!empty($content)) {
            if (!empty($content['style'])) {
                $user_data_growth += 20;
            }
            if (!empty($content['frequency'])) {
                $user_data_growth += 20;
            }
        }
        return $user_data_growth;
    }

    /**
     * 获取用户成长值
     * @author yang
     * @param $user_id
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

    /**
     * 用户列表获取
     * @param array $params
     * @param int $size
     * @return mixed
     */
    public function getListByParams(array $params, int $size = 20)
    {
        return $this->userDao->getUserListByParams($params,['user_id','phone'],$size);
    }

    /**
     * @param $data
     * @return mixed
     * @throws MysqlException
     */
    public function set_strength_update_record($data)
    {
        return $this->userDao->setUserStrengthRecord($data);
    }

    /**
     * @param $user_id
     * @param $old_time
     * @param $end_time
     * @return mixed
     */
    public function get_strength_update_record($user_id,$old_time,$end_time)
    {
        return $this->userDao->getUserStrengthRecord($user_id,$old_time,$end_time);
    }

    /**
     * 获取最后一次的实商过期记录
     * @param $user_id
     * @return mixed
     */
    public function get_last_strength($user_id)
    {
        return $this->userDao->getLastUserStrength($user_id);
    }

    /**
     * 获取最后一次的实商过期记录
     * @param $id
     * @return mixed
     */
    public function get_strength_by_id($id)
    {
        return $this->userDao->getUserStrengthById($id);
    }

    /**
     * 体验记录
     * @param $user_id
     * @return mixed
     */
    public function get_strength_experience_info($user_id)
    {
        return $this->userDao->getUserStrengthExperienceInfo($user_id);
    }

    /**
     * 时尚体验过期
     * @param $id
     * @param $data
     * @return mixed
     */
    public function strength_receive_expired($id,$data)
    {
        $data['is_cancel'] = 1;
        $data['is_expire'] = 1;
        $data['cancel_time'] = time();
        return $this->userDao->updateStrengthExperience($id,$data);
    }

    /**
     * 实商体验类目
     * @param int $experience_id 体验规则id
     * @param string $experience_key 体验规则标识
     * @return mixed
     */
    public function get_experience_info(int $experience_id,string $experience_key = '')
    {
        return $this->userDao->experienceInfo($experience_id,$experience_key);
    }

    /**
     * 增值服务订单详情
     * @param string $order_num
     * @return mixed
     */
    public function get_appreciation_order(string $order_num)
    {
        return $this->userDao->appreciationOrderInfo($order_num);
    }

    /**
     * 增值服务产品获取
     * @param int $product_id
     * @return mixed
     */
    public function get_appreciation_product(int $product_id)
    {
        return $this->userDao->getAppreciationProduct($product_id);
    }

    /**
     * 实商活动奖励获取
     * @param int $activity_id
     * @param int $safe_price
     * @return mixed
     */
    public function get_appreciation_presentation(int $activity_id, int $safe_price)
    {
        return $this->userDao->getStrengthActivityPresentation($activity_id,$safe_price);
    }

    /**
     * 实商活动领取记录添加
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function save_appreciation_activity_receive(array $data)
    {
        return $this->userDao->saveStrengthPresentationReceive($data);
    }

    /**
     * 实商开通/续期
     * @param array $data
     * @param int $id
     * @return mixed
     * @throws MysqlException
     */
    public function save_user_strength(array $data, int $id)
    {
        return $this->userDao->saveStrengthInfo($data,$id);
    }

    /**
     * 实商体验领取数获取
     * @param int $user_id
     * @param int $id
     * @return mixed
     */
    public function get_strength_experience_count(int $user_id, int $id)
    {
        return $this->userDao->getExperienceReceiveCount($user_id,$id);
    }

    /**
     * 实商体验领取
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function save_strength_experience_receive(array $data)
    {
        return $this->userDao->saveExperienceReceiveRecord($data);
    }

    /**
     * 保证金用户列表
     * @param array $params
     * @return mixed
     */
    public function get_safe_price_uid_list(array $params)
    {
        return $this->userDao->getSafePriceList($params);
    }

    /**
     * 多用户保证金缴纳次数获取
     * @param array $user_ids
     * @return mixed
     */
    public function get_safe_price_ulist_times(array $user_ids)
    {
        return $this->userDao->getSafePriceTimes($user_ids);
    }

    /*
     * 修改用户钱包余额
     * @param int $user_id
     * @param float $balance_price
     * @return mixed
     * @throws MysqlException
     */
    public function updateUserWallet(int $user_id, float $balance_price)
    {
        return $this->userDao->updateUserWallet($user_id, $balance_price);
    }

    /**
     * 修改用户钱包余额
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function addUserWalletRecord(array $data)
    {
        return $this->userDao->addUserWalletRecord($data);
    }

    /**
     * 最新登录版本号获取
     * @param $user_id
     * @return mixed
     */
    public function getUserLoginVersion($user_id)
    {
        return $this->userDao->getUserLastLogin($user_id);
    }

    /**
     * 微信公众号openid获取
     * @param int $user_id
     * @return string
     */
    public function getUserOpenId(int $user_id)
    {
        return $this->userDao->getUserOpenId($user_id);
    }
}
