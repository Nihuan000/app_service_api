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
use App\Models\Data\TagData;
use App\Models\Data\OrderData;
use App\Models\Data\BuyRelationTagData;
use App\Models\Data\UserSubscriptionTagData;
use Swoft\Bean\Annotation\Bean;
use Swoft\Db\Db;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Bean\Annotation\Inject;
use Zend\Stdlib\Request;

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
     * @var OrderData
     */
    private $orderData;

    /**
     * @Inject()
     * @var TagData
     */
    private $TagData;

    /**
     * @Inject()
     * @var BuyRelationTagData
     */
    private $BuyRelationTagData;

    /**
     * @Inject()
     * @var UserSubscriptionTagData
     */
    private $userSubscriptionTagData;


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
        $user_count = $this->userData->getUserCountByParams($params);
        Log::info('user_count:' . $user_count);
        $pages = ceil($user_count/$limit);
        $ready_ids = [];
        if($pages >= 0){
            $last_id = 0;
            for ($i = 0;$i < $pages; $i++){
                $supplierAll = [];
                $params[] = ['user_id','>',$last_id];
                $list = $this->userData->getUserDataByParams($params, $limit);
                Log::info('user_list:' . json_encode($list));
                foreach ($list as $item) {
                    $user_id = $item['userId'];
                    $data['user_id'] = $user_id;
                    //去重判断
                    if(in_array($data['user_id'],$ready_ids)){
                        continue;
                    }
                    $data['days_type'] = $day_type;
                    //登录天数获取
                    $login_days = $this->userData->getUserLoginTimes($user_id, $last_day_time);
                    $data['login_days'] = count($login_days);
                    //未读采购数获取
                    $unread_count = 0;
                    if(!empty($login_days)){
                        $unread_list = array_diff($day_list, $login_days);
                        if(!empty($unread_list)){
                            $unread_count = $this->userData->getUserSubscriptBuyCount($user_id,$unread_list);
                        }
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
                    Log::info('user_list:' . json_encode($data));
                    $supplierAll[] = $data;
                    $ready_ids[] = $data['user_id'];
                    $last_id = $item['userId'];
                }
                $this->userData->saveSupplierData($supplierAll);
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
                        if(isset($match_record_list[$shop[0]]) || $shop[0] == $params['user_id']){
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

    /**
     * 实商交易额更新
     * @param $user_id
     * @param $order_num
     * @param $total_amount
     * @param $pay_time
     * @return bool|\Swoft\Core\ResultInterface
     * @throws \Swoft\Db\Exception\MysqlException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function strengthUserOrderTotal($user_id,$order_num,$total_amount,$pay_time)
    {
        $plusRes = true;
        $strength_info = $this->userData->getUserStrengthInfo($user_id);
        if(!empty($strength_info)){
            $activity_info = $this->userData->getStrengthActivity($pay_time,2);
            if(!empty($activity_info)){
                $checkRec = $this->userData->checkStrengthOrderRecord($user_id,$order_num);
                if(!$checkRec){
                    Db::beginTransaction();
                    $order_total = $strength_info['total_amount'] + $total_amount;
                    $params = [
                        'total_amount' => $order_total,
                        'update_time' => $pay_time
                    ];
                    $totalRes = $this->userData->userStrengthPlus($user_id,$strength_info['id'],$params);
                    $recordRes = $this->userData->saveStrengthOrder($user_id,$order_num,$total_amount,$pay_time,$strength_info['total_amount']);
                    if($totalRes && $recordRes){
                        Db::commit();
                    }else{
                        Db::rollback();
                        $plusRes = false;
                    }
                }
            }
        }
        return $plusRes;
    }

    /**
     * 根据采购标签推荐供应商
     * @author yang
     * @param $buy_id
     * @return array
     */
    public function buyTagRecommend($buy_id)
    {
        //1.获取标签id
        $tag_ids = $this->BuyRelationTagData->getRealtionTagByIds([$buy_id],['tag_id']);
        if (!empty($tag_ids)){
            $tag_ids_arr = [];
            foreach ($tag_ids as $value) {
                $tag_ids_arr[] = $value['tagId'];
            }
            //2.根据标签获取符合条件的全部供应商
            if (!empty($tag_ids_arr)){
                $user_ids = $this->userSubscriptionTagData->getUserIds($tag_ids_arr);
                return $user_ids;
            }
        }
        return [];
    }

    /**
     * 成长值记录
     * @author yang
     * @param array $params
     * @param array $rule
     * @return bool
     * @throws \Swoft\Db\Exception\DbException
     */
    public function growth($params, $rule)
    {
        $user_id = $params['user_id'];
        $data = [
            'user_id' => $user_id,
            'growth_id' => $rule['id'],
            'growth' => $rule['value'],
            'name' => $params['name'],
            'title' => $rule['title'],
            'add_time' => time(),
            'update_time' => time(),
            'remark' => $rule['remark'],
            'version' => 1,
            'status' => 1,
            'operate_id' => $params['operate_id'],
        ];

        $user_info = $this->userData->getUserInfo($user_id);//用户信息


        //开启事务
        Db::beginTransaction();
        if ($rule['name'] == 'transaction_limit'){
            $user_growth_record_one = $this->userData->userGrowthRecordOne($user_id, 'transaction_limit');
            $total_order_price = $this->orderData->getOrderAllPrice($user_id);//交易成功额度
            $growth = intval($total_order_price/1000)*10;//新的交易成功成长值

            if (isset($user_growth_record_one)){

                $add_growth = $growth-$user_growth_record_one['growth'];//应该增加的成长值
                $user_growth_record = $this->userData->userGrowthUpdate($add_growth, $user_id);//更新成长值
                $user_growth = $this->userData->userGrowthRecordUpdate(['growth'=>$growth], $user_id, $rule['name']);//更新记录
            }else{

                $data['growth'] = $growth;
                $user_growth = $this->userData->userGrowthUpdate($growth, $user_id);//更新成长值
                $user_growth_record = $this->userData->userGrowthRecordInsert($data);//增加记录
            }

        }else if ($rule['name'] == 'personal_data'){

            //安卓ios区分计算不同的资料完善率
            $user_growth_record_one = $this->userData->userGrowthRecordOne($user_id, 'personal_data');
            if (!empty($params['system'])){
                if ($params['system']==1){
                    Log::info('用户:'.$user_id.' 安卓');
                    //安卓
                    $user_data_growth = $this->userData->androidUserDate($user_id,$user_info['mainProduct']);
                }else{
                    Log::info('用户:'.$user_id.' ios');
                    //ios
                    $user_data_growth = $this->get_completion_rate($user_id,$user_info['mainProduct']);
                }
                if (isset($user_growth_record_one)){
                    $add_growth = $user_data_growth-$user_growth_record_one['growth'];//应该增加的成长值
                    Log::info('用户:'.$user_id.' 有记录更新  当前成长值'.$user_growth_record_one['growth'].' 当前最新成长值'.$user_data_growth);
                    $user_growth_record = $this->userData->userGrowthUpdate($add_growth, $user_id);//更新成长值
                    $user_growth = $this->userData->userGrowthRecordUpdate(['growth'=>$user_data_growth], $user_id, $rule['name']);//更新记录
                }else{
                    $data['growth'] = $user_data_growth;
                    $user_growth = $this->userData->userGrowthUpdate($user_data_growth, $user_id);//更新成长值
                    $user_growth_record = $this->userData->userGrowthRecordInsert($data);//增加记录
                }
            }else{
                //参数错误
                return false;
            }

        }else{

            $user_growth = $this->userData->userGrowthUpdate((int)$rule['value'], $user_id);//更新成长值
            $user_growth_record = $this->userData->userGrowthRecordInsert($data);//增加记录
        }

        $user_level_update = true;
        $user_growth_switch = $this->userData->getSetting('user_growth_switch');
        if ($user_growth_switch){
            Log::info('用户:'.$user_id.' 更新等级');
            //更新等级
            $growth_rule = $this->userData->userGrowth($user_id);
            $level = $this->userData->getUserLevelRule($growth_rule);
            $user_level_update = $this->userData->userUpdate(['level'=>$level['level_sort']], $user_id);
            if ($user_info['level'] < $level['level_sort']){
                //降级
                $this->redis->hset('lifting_level', 'user:'.$user_id, 'plus');
                Log::info('用户:'.$user_id.' 升级为:'.$level['level_sort']);
            }else if ($user_info['level'] > $level['level_sort']){
                //升级
                $this->redis->hset('lifting_level', 'user:'.$user_id, 'minus');
            }
        }

        if($user_growth_record !== false && $user_growth !== false && $user_level_update !== false){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }

    /**
     * ios资料完善率
     * @author yang
     * @param int $user_id
     * @param string $main_product
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function get_completion_rate($user_id,$main_product){
        $base_count = 3;
        $completion_count = 1;
        $purchaser_role_type = $this->private_get_user_purchaser_role_type($user_id);//获取用户身份，和身份对应的背景
        if(empty($purchaser_role_type)){
            return 0;
        }else{
            $purchaser_industry = $this->private_get_user_purchaser_industry($user_id);//获取主营行业
            $web_factory_url = $this->userData->getUserAttribute($user_id);//品牌网站
            $work_img = $this->private_get_work_img($user_id);//获取环境图
            $website_url = $this->userData->getUserPurchaserRoleWebsiteUrl($user_id);//网店地址
            $title= $this->get_title($purchaser_role_type);
            foreach ($title as $key =>$value){
                if(!empty($value)){
                    $base_count ++;
                }
            }
            $purchaser_industry_info = $this->handle_special_industry($user_id);
            if(!empty($purchaser_industry) || $purchaser_industry_info['is_special'] == true){
                $completion_count ++;
            }
            if(!empty($main_product)){
                $completion_count ++;
            }
            if(in_array($purchaser_role_type[0]['background'][0]['name'],['我是品牌企业'])){
                $base_count ++;
                if(!empty($web_factory_url)){
                    $completion_count ++;
                }
            }

            if(  $purchaser_role_type[0]['name'] !='个人自用'){
                $base_count ++;
                if(!empty($website_url) ){
                    $completion_count ++;
                }
            }
            if($base_count >4 && !empty($work_img["img_first_list"]) ){
                $completion_count ++;
            }
            if($base_count >5 && !empty($work_img["img_second_list"]) ){
                $completion_count ++;
            }
            return (float)round($completion_count / $base_count * 100 , 0 );
        }
    }

    /**
     * 获取采购身份
     * @Author yang
     * @Date 19-03-25
     */
    private function private_get_user_purchaser_role_type($user_id){
        $content = $this->userData->getUserPurchaserRole($user_id);
        if (!empty($content)) {
            foreach ($content as $key => $val) {
                $content[$key]['id'] = (int)$val['id'];
                $content[$key]['parent_id'] = (int)$val['parent_id'];
                $content[$key]['parent_name'] = is_null($val['parent_name']) ? '' : $val['parent_name'];
                if($val['parent_name'] == "生产制造企业"){
                    $background = $this->userData->getUserPurchaserRoleBackground($user_id,1);
                }else if($val['parent_name'] == "加工制造企业"){
                    $background = $this->userData->getUserPurchaserRoleBackground($user_id,2);
                }else if($val['parent_name'] == "批发商"){
                    $background = $this->userData->getUserPurchaserRoleBackground($user_id,3);
                }else{
                    $background = [];
                }
                $background = empty($background) ? [] :$background;
                foreach ($background as $ke => $value){
                    if($value['name'] == "我是品牌企业"){
                        $background[$ke]['is_show_brand'] = 1;
                    }else{
                        $background[$ke]['is_show_brand'] = 0;
                    }
                    $background[$ke]['id'] = (int)$value['id'];
                    $background[$ke]['role_id'] = (int)$value['role_id'];
                }
                $content[$key]['background'] = $background;
            }
            $result = $content;
        } else {
            $result = [];
        }
        return $result;
    }


    /**
     * 获取主营行业
     * @Author yang
     * @Date 19-03-25
     */
    private function private_get_user_purchaser_industry($user_id){
        $this->userData->getUserPurchaserIndustry($user_id);
        if (!empty($content)) {
            foreach ($content as $key => $val) {
                $content[$key]['id'] = (int)$val['id'];
                $content[$key]['parent_id'] = (int)$val['parent_id'];
                $content[$key]['parent_name'] = is_null($val['parent_name']) ? '' : $val['parent_name'];
            }
            $result['main_industry'] = $content;
        } else {
            $result['main_industry'] = [];
        }
        return $result;
    }

    /**
     * 获取环境图
     * @Author yang
     * @Date 19-03-25
     */
    private function private_get_work_img($user_id){
        $img_first_list_img = $this->userData->getUserPurchaserRoleWorkImg($user_id,1);
        $img_second_list_img = $this->userData->getUserPurchaserRoleWorkImg($user_id,2);
        $img_first_list = [];
        $img_second_list = [];
        if (!empty($img_first_list_img)){
            foreach ($img_first_list_img as $value){
                $img_first_list[] = $value['img'];
            }
        }
        if (!empty($img_second_list_img)){
            foreach ($img_second_list_img as $value){
                $img_second_list[] = $value['img'];
            }
        }
        $img_list["img_first_list"] = empty($img_first_list) ? [] : $img_first_list;
        $img_list["img_second_list"] = empty($img_second_list) ? [] : $img_second_list ;
        return $img_list;
    }

    private function  get_title($purchaser_role_type){
        if($purchaser_role_type[0]['parent_name'] == "生产制造企业"){
            $data = ['我的服装厂',''];
        }else if($purchaser_role_type[0]['parent_name'] == "批发商"){
            $data = ['我的档口','我的仓库'];
            if($purchaser_role_type[0]['background'][0]['name'] == "我有档口"){
                $data = ['我的档口',''];
            }
        }else if($purchaser_role_type[0]['parent_name'] == "加工制造企业"){
            $data = ['我的厂房机械',''];
        }else if($purchaser_role_type[0]['parent_name'] == "代找布公司"){
            $data = ['公司环境和名片',''];
        }else {
            $data = ['',''];
        }
        if($purchaser_role_type[0]['parent_name'] == "加工制造企业" && $purchaser_role_type[0]['background'][0]['name'] == "我是品牌企业"){
            $data = ['我的厂房机械','办公环境'];
        }
        if($purchaser_role_type[0]['background'][0]['name'] == "我是品牌企业" && $purchaser_role_type[0]['parent_name'] != "加工制造企业" ){
            $data = ['我的服装厂','办公环境'];
        }
        return $data;
    }


    private function handle_special_industry($user_id){
        $purchaser_role_type = $this->private_get_user_purchaser_role_type($user_id);
        if(empty($purchaser_role_type)){
            return ['is_special'=>false , 'main_industry'=>[]];
        }else{
            if(in_array($purchaser_role_type[0]['parent_name'],['代找布公司','批发商','个人自用','加工制造企业'])){
                $main_industry = [['id'=>0,'name'=>$purchaser_role_type[0]['name'],'parent_id'=>$purchaser_role_type[0]['parent_id'],'parent_name'=>$purchaser_role_type[0]['parent_name']]];
                return ['is_special'=>true , 'main_industry'=>$main_industry];
            }else{
                return ['is_special'=>false , 'main_industry'=>[]];
            }
        }

    }

}