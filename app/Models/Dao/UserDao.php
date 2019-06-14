<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Dao;

use App\Models\Entity\SupplierDataStatistic;
use Swoft\Bean\Annotation\Bean;
use App\Models\Entity\User;
use App\Models\Entity\UserGrowthRecord;
use App\Models\Entity\UserGrowthRule;
use App\Models\Entity\UserGrowth;
use Swoft\Core\ResultInterface;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
use Swoft\Db\Query;

/**
 * 用户数据对象
 * @Bean()
 * @uses UserDao
 * @author Nihuan
 */
class UserDao
{
    /**
     * 主键获取用户信息
     * @author Nihuan
     * @param int $uid
     * @return mixed
     */
    public function getUserInfoByUid(int $uid)
    {
        return User::findById($uid)->getResult();
    }


    /**
     * 根据id列表获取field
     * @author Nihuan
     * @param array $user_ids
     * @param array $fields
     * @return mixed
     */
    public function getInfoByUids(array $user_ids, array $fields)
    {
        return User::findAll(['user_id' => $user_ids],['field' => $fields])->getResult();
    }

    /**
     * @param int $user_id
     * @return mixed
     * @throws DbException
     */
    public function getUserTagByUid(int $user_id)
    {
        $list = Db::query("select user_id, tag_id,tag_name, parent_id, parent_name, top_name, top_id from sb_user_subscription_tag where user_id= {$user_id}")->getResult();
        return $list;
    }

    /**
     * 更新用户信息
     * @author yang
     * @param $params
     * @param $user_id
     * @return mixed
     */
    public function userUpdate($params,$user_id)
    {
        return User::updateOne($params, ['user_id' => $user_id])->getResult();
    }

    /**
     * author: nihuan
     * @param int $is_delete
     * @param int $type
     * @return array
     * @throws DbException
     */
    public function getTestersInfo(int $is_delete, int $type)
    {
        $user_list = [];
        $list = Db::query("select uid from sb_agent_user WHERE is_delete = {$is_delete} AND type = {$type}")->getResult();
        if(!empty($list)){
            foreach ($list as $user) {
                $user_list[] = $user['uid'];
            }
        }

        return $user_list;
    }

    /**
     * 返回包含某二级标签用户
     * @param array $user_id
     * @param string $tag
     * @return mixed
     */
    public function getUserListBySecTag(array $user_id, string $tag)
    {
        return Query::table('sb_user_subscription_tag')
            ->whereIn('user_id',$user_id)
            ->where('parent_name',$tag)
            ->groupBy('user_id')
            ->get(['user_id','top_name'])
            ->getResult();
    }

    /**
     * 实商&保证金用户获取
     * @param array $params
     * @param array $field
     * @return mixed
     * @throws DbException
     */
    public function getUserStrengthList(array $params = [], array $field = [])
    {
        $queryModel = Query::table('sb_user','u');
        $queryModel->leftJoin('sb_user_strength',"u.user_id = t.user_id",'t');
        $queryModel->openWhere();
        $queryModel->openWhere();
        $queryModel->where('t.is_expire',0);
        $queryModel->where('t.pay_for_open',1);
        $queryModel->closeWhere();
        $queryModel->orWhere('u.safe_price',3000,'>');
        $queryModel->closeWhere();
        $queryModel->groupBy('u.user_id');
        if(!empty($params)){
            if(isset($params['user_ids'])){
                $queryModel->whereIn('u.user_id',$params['user_ids']);
            }

            if(isset($params['last_time'])){
                $queryModel->where('u.last_time',$params['last_time'],'>');
            }
        }
        if(!empty($field)){
            return $queryModel->get($field)->getResult();
        }else{
            return $queryModel->get(['u.user_id'])->getResult();
        }
    }

    /**
     * 配置获取
     * @param $keywrod
     * @return mixed
     */
    public function getSettingInfo($keywrod)
    {
        return Query::table('sb_setting')->where('keyword',$keywrod)->where('status',1)->get(['value','value_type'])->getResult();
    }

    /**
     * 内部账号
     * @param int $type
     * @return array
     */
    public function getAgentInfo($type = 5)
    {
        $list = [];
        $agent_list = Query::table('sb_agent_user');
        $agent_list->where('is_delete',0);
        if(!empty($type)){
            $agent_list->where('type',$type);
        }
        $agent_user = $agent_list->get(['uid'])->getResult();
        if(!empty($agent_user)){
            foreach ($agent_user as $item) {
                $list[] = $item['uid'];
            }
        }
        return $list;
    }

    /**
     * 返回指定用户指定字段
     * @param array $params
     * @param array $fields
     * @param int $limit
     * @return mixed
     */
    public function getUserListByParams(array $params, array $fields, $limit = 20)
    {
        return User::findAll($params,['fields' => $fields, 'limit' => $limit, 'orderby' => ['user_id' => 'asc']])->getResult();
    }

    /**
     * 获取指定条件的用户数
     * @param array $params
     * @return mixed
     */
    public function getUserCountByParams(array $params)
    {
        return User::count('user_id',$params)->getResult();
    }

    /**
     * 返回用户登录天数
     * @param int $user_id
     * @param int $last_time
     * @return ResultInterface
     * @throws DbException
     */
    public function getUserLoginDays(int $user_id, int $last_time)
    {
        $table = 'sb_login_log_' . date('Y');
        $current_time = strtotime(date('Y-m-d'));
        return Db::query("select from_unixtime(addtime,'%Y-%m-%d') as addtime from {$table} where user_id = {$user_id} AND addtime >= {$last_time} AND addtime < {$current_time} group by from_unixtime(addtime,'%Y-%m-%d')")->getResult();
    }

    /**
     * 用户回复数据
     * @param int $user_id
     * @param int $last_time
     * @return mixed
     * @throws DbException
     */
    public function getUserChatDuration(int $user_id, int $last_time)
    {
        $current_time = strtotime(date('Y-m-d'));
        $list = Db::query("select avg(avg_chat_duration) as avg_chat_duration,sum(un_reply_count) as un_reply_count from sb_chat_user_dialog WHERE user_id = {$user_id} AND record_date >= {$last_time} AND add_time < {$current_time}")->getResult();
        return $list;
    }

    /**
     * 访客列表
     * @param int $user_id
     * @param int $last_time
     * @return mixed
     */
    public function getUserVisitData(int $user_id, int $last_time)
    {
        $current_time = strtotime(date('Y-m-d'));
        return Query::table('sb_user_visit')->where('visit_id',$user_id)->where('visit_time',$last_time,'>=')->where('visit_time',$current_time,'<')->groupBy('user_id')->get(['user_id'])->getResult();
    }

    /**
     * 用户对话数据
     * @param int $user_id
     * @param int $last_time
     * @return mixed
     */
    public function getUserChatStatisitcs(int $user_id, int $last_time)
    {
        $current_time = strtotime(date('Y-m-d'));
        return Query::table('sb_chat_user_statistics')->where('from_id',$user_id)->orWhere('target_id',$user_id)->andWhere('record_date',$last_time,'>=')->where('record_date',$current_time,'<')->get(['from_id','target_id'])->getResult();
    }

    /**
     * 供应商数据写入
     * @param $data
     * @return mixed
     */
    public function saveSupplierData($data)
    {
        return SupplierDataStatistic::batchInsert($data)->getResult();
    }

    /**
     * 供应商数据更新
     * @param array $data
     * @param array $where
     * @return mixed
     */
    public function updateSupplierData(array $data, array $where)
    {
        return SupplierDataStatistic::updateAll($data, ['user_id' => $where])->getResult();
    }

    /**
     * 符合条件的数据
     * @param $params
     * @param $limit
     * @return mixed
     */
    public function getSupplierData($params,$limit)
    {
        return SupplierDataStatistic::findAll($params,['fields' => ['sds_id','user_id'],'limit' => $limit,'orderby' => ['sds_id' => 'asc']])->getResult();
    }

    /**
     * 符合条件的数据量
     * @param $params
     * @return mixed
     */
    public function getSupplierCount($params)
    {
        return SupplierDataStatistic::count('sds_id',$params)->getResult();
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function getUserStrengthInfo($user_id)
    {
        $queryModel = Query::table('sb_user_strength');
        $queryModel->where('is_expire',0);
        $queryModel->where('user_id',$user_id);
        $queryModel->where('level',0,'>');
        return $queryModel->one(['*'])->getResult();
    }

    /**
     * 实商活动
     * @param $time
     * @param $activity_type
     * @return mixed
     */
    public function getStrengthActivity($time,$activity_type)
    {
        return Query::table('sb_user_strength_activity')
            ->where('start_time',$time,'<=')
            ->where('end_time',$time,'>')
            ->where('activity_type',$activity_type)
            ->where('is_enable',1)
            ->get()->getResult();
    }

    /**
     * 实商交易额更新
     * @param $user_id
     * @param $strength_id
     * @param $params
     * @return ResultInterface
     */
    public function userStrengthPlus($user_id, $strength_id,$params)
    {
        return Query::table('sb_user_strength')->where('user_id',$user_id)->where('id',$strength_id)->update($params)->getResult();
    }

    /**
     * @param $user_id
     * @param $order_num
     * @param $total_amount
     * @param $take_time
     * @param $prev_amount
     * @return mixed
     * @throws MysqlException
     */
    public function strengthOrderRecord($user_id, $order_num, $total_amount, $take_time, $prev_amount)
    {
        $insert = [
            'user_id' => $user_id,
            'order_num' => $order_num,
            'total_amount' => $total_amount,
            'take_time' => $take_time,
            'prev_amount' => $prev_amount,
            'record_time' => time()
        ];
        return Query::table('sb_user_strength_order_list')->insert($insert)->getResult();
    }

    /**
     * 实商交易记录
     * @param $order_num
     * @param $user_id
     * @return ResultInterface
     */
    public function checkStrengthOrderRecord($order_num,$user_id)
    {
        return Query::table('sb_user_strength_order_list')->where('user_id',$user_id)->where('order_num',$order_num)->count('usol_id','scount')->getResult();
    }

    /**
     * 成长值规则
     * @author yang
     * @param $name
     * @return mixed
     */
    public function userGrowthRule($name)
    {
        return UserGrowthRule::findOne(['name' => $name, 'user_type' => 1, 'status' => 1], ['fields' => ['id', 'name', 'title', 'value', 'remark']])->getResult();
    }

    /**
     * 成长值记录
     * @author yang
     * @param $params
     * @return mixed
     */
    public function UserGrowthRecordInsert($params)
    {
        $UserGrowthRecord   = new UserGrowthRecord();
        return $UserGrowthRecord->fill($params)->save()->getResult();
    }

    /**
     * 成长值记录查询
     * @author yang
     * @param $user_id
     * @param $name
     * @return array
     */
    public function UserGrowthRecordOne($user_id, $name)
    {
        return UserGrowthRecord::findOne(['user_id'=>$user_id, 'name'=>$name, 'status'=>1], ['fields' => ['growth']])->getResult();
    }

    /**
     * 成长值记录更新
     * @author yang
     * @param $params
     * @param $user_id
     * @param $name
     * @return mixed
     */
    public function userGrowthRecordUpdate($params, $user_id, $name)
    {
        return UserGrowthRecord::updateOne($params, ['user_id'=>$user_id, 'name'=>$name, 'status'=>1])->getResult();
    }

    /**
     * 成长值记录
     * @author yang
     * @param $params
     * @param $user_id
     * @return mixed
     */
    public function UserGrowthUpdate($params,$user_id)
    {
        return UserGrowth::updateOne($params, ['user_id' => $user_id])->getResult();
    }

    /**
     * 成长值记录
     * @author yang
     * @param $user_id
     * @return mixed
     */
    public function UserGrowth($user_id)
    {
        return UserGrowth::findOne(['user_id' => $user_id], ['fields' => ['growth', 'update_time']])->getResult();
    }

    /**
     * 成长值表新增用户
     * @author yang
     * @param $user_id
     * @return mixed
     */
    public function UserGrowthAdd($user_id)
    {
        $data = [
            'user_id'=>$user_id,
            'growth'=>0,
            'add_time'=>time(),
            'update_time'=>time(),
        ];
        $user   = new UserGrowth();
        $result = $user->fill($data)->save()->getResult();
        return $result;
    }

    /**
     * 成长值记录
     * @author yang
     * @param int $post_user_id
     * @param int $limit
     * @return array
     */
    public function getUserList($post_user_id, $limit)
    {
        return User::findAll([['role', 'in', [1,5]],['user_id'=> $post_user_id]],['fields' => ['user_id','main_product','certification_type','phone_type'], 'limit' => $limit, 'orderby' => ['user_id' => 'asc']])->getResult();

    }

    /**
     * 采购商成长值查等级
     * @author yang
     * @param int $growth
     * @return array
     */
    public function getUserLevelRule($growth)
    {
        return Query::table('sb_user_level_rule')->where('min_growth',$growth,'<=')->where('max_growth',$growth,'>=')->where('user_type',3)->one()->getResult();
    }

    /**
     * 获取评价数
     * @author yang
     * @param  int $user_id
     * @return int
     */
    public function getReviewCount($user_id)
    {
        return Query::table('sb_order_shop_score')->where('uid',$user_id)->where('status',1)->where('message', '', '!=')->count('sco_id')->getResult();
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
        return Query::table('sb_order_shop_score')
            ->innerJoin('sb_order_shop_review','sb_order_shop_score.sco_id = sb_order_shop_review.score_id')
            ->where('rating',5.0)
            ->whereIn('audit_status',[0,1])
            ->where('uid',$user_id)
            ->where('status',1)
            ->count('sco_id')->getResult();
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
        return Query::table('sb_order_shop_score')
            ->innerJoin('sb_order_shop_review','sb_order_shop_score.sco_id = sb_order_shop_review.score_id')
            ->where('rating',1.0)
            ->whereIn('audit_status',[0,1])
            ->where('uid',$user_id)
            ->where('status',1)
            ->count('sco_id')->getResult();
    }

    /**
     * 查询采购身份
     * @param $user_id
     * @return array
     * @throws DbException
     * @author yang
     */
    public function getUserPurchaserRole($user_id)
    {
        return Query::table('sb_user_purchaser_role','a')
            ->leftJoin('sb_user_purchaser_role',"a.parent_id = b.id",'b')
            ->where('a.user_id',$user_id)
            ->where('a.is_delete',0)
            ->get(['a.role_id as id', 'a.role_name as name', 'a.parent_id', 'b.role_name as parent_name'])
            ->getResult();
    }

    /**
     * 查询主营行业
     * @param $user_id
     * @return array
     * @throws DbException
     * @author yang
     */
    public function getUserPurchaserIndustry($user_id)
    {
        return Query::table('sb_user_purchaser_industry','a')
            ->leftJoin('sb_user_purchaser_industry',"a.parent_id = b.id",'b')
            ->where('a.user_id',$user_id)
            ->where('a.is_delete',0)
            ->get(['a.industry_id as id', 'a.industry_name as name', 'a.parent_id', 'b.industry_name as parent_name'])
            ->getResult();
    }

    /**
     * 查询公司信息
     * @author yang
     * @param $user_id
     * @return array
     */
    public function getUserCompany($user_id)
    {
        return Query::table('sb_user_company')
            ->where('user_id',$user_id)
            ->one()->getResult();
    }

    /**
     * 获取品牌网站
     * @author yang
     * @param $user_id
     * @return array
     */
    public function getUserAttribute($user_id)
    {
        return Query::table('sb_user_attribute')
            ->where('user_id',$user_id)
            ->one()->getResult();
    }

    /**
     * 获取环境图
     * @author yang
     * @param $user_id
     * @return array
     */
    public function getUserPurchaserRoleWorkImg($user_id,$type)
    {
        return Query::table('sb_user_purchaser_role_work_img')
            ->where('user_id',$user_id)
            ->where('type',$type)
            ->get()
            ->getResult();
    }

    /**
     * 网店地址
     * @author yang
     * @param $user_id
     * @return array
     */
    public function getUserPurchaserRoleWebsiteUrl($user_id)
    {
        return Query::table('sb_user_purchaser_role_website_url')
            ->where('user_id',$user_id)
            ->where('is_delete',0)
            ->where('is_audit',1)
            ->where('url_type',20,'!=')
            ->get()
            ->getResult();
    }

    /**
     * 采购身份背景表
     * @author yang
     * @param $user_id
     * @param $role_type
     * @return array
     */
    public function getUserPurchaserRoleBackground(int $user_id,int $role_type)
    {
        return Query::table('sb_user_purchaser_role_background','upr')
            ->where('user_id',$user_id)
            ->where('is_delete',0)
            ->where('role_type',$role_type)
            ->get(['id','upr.role_background_name as name','role_type','role_id'])
            ->getResult();
    }

    /**
     * 实商变更记录
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function setUserStrengthRecord(array $data)
    {
        $data['add_time'] = time();
        return Query::table('sb_user_strength_change_record')->insert($data)->getResult();
    }

    /**
     * 实商变更重复记录判断
     * @param $user_id
     * @param $old_time
     * @param $end_time
     * @return mixed
     */
    public function getUserStrengthRecord($user_id,$old_time,$end_time)
    {
        return Query::table('sb_user_strength_change_record')
            ->where('user_id',$user_id)
            ->where('old_end_time',$old_time)
            ->where('new_end_time',$end_time)
            ->count()
            ->getResult();
    }

    /**
     * 开通实商历史记录
     * @param $user_id
     * @return mixed
     */
    public function getLastUserStrength($user_id)
    {
        return Query::table('sb_user_strength')
            ->where('user_id',$user_id)
            ->where('is_expire',1)
            ->orderBy('id','DESC')
            ->one(['end_time','pay_for_open'])
            ->getResult();
    }
}
