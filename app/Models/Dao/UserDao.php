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
use Swoft\Db\Db;
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
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserTagByUid(int $user_id)
    {
        $list = Db::query("select user_id, tag_id,tag_name, parent_id, parent_name, top_name, top_id from sb_user_subscription_tag where user_id= {$user_id}")->getResult();
        return $list;
    }


    /**
     * author: nihuan
     * @param int $is_delete
     * @param int $type
     * @return array
     * @throws \Swoft\Db\Exception\DbException
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
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserStrengthList(array $params = [], array $field = [])
    {
        $queryModel = Query::table('sb_user_strength','t');
        $queryModel->leftJoin('sb_user',"u.user_id = t.user_id",'u');
        $queryModel->where('t.is_expire',0);
        $queryModel->where('t.level',0,'>');
        $queryModel->where('u.safe_price',0,'>');
        $queryModel->where('t.remark','');
        $queryModel->groupBy('t.user_id');
        if(!empty($params)){
            if(isset($params['user_ids'])){
                $queryModel->whereIn('t.user_id',$params['user_ids']);
            }
        }
        if(!empty($field)){
            return $queryModel->get($field)->getResult();
        }else{
            return $queryModel->get(['t.user_id'])->getResult();
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
     * 返回指定用户指定字段
     * @param array $params
     * @param array $fields
     * @param int $limit
     * @return mixed
     */
    public function getUserListByParams(array $params, array $fields, int $limit = 20)
    {
        return User::findAll($params,['fields' => $fields, 'limit' => $limit])->getResult();
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
     * @return \Swoft\Core\ResultInterface
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserLoginDays(int $user_id, int $last_time)
    {
        $table = 'sb_login_log_' . date('Y');
        return Db::query("select from_unixtime(addtime,'%Y-%m-%d') as addtime from {$table} where user_id = {$user_id} AND addtime >= {$last_time} group by from_unixtime(addtime,'%Y-%m-%d')")->getResult();
    }

    /**
     * 用户回复数据
     * @param int $user_id
     * @param int $last_time
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserChatDuration(int $user_id, int $last_time)
    {
        $list = Db::query("select avg(avg_chat_duration) as avg_chat_duration,sum(un_reply_count) as un_reply_count from sb_chat_user_dialog WHERE user_id = {$user_id} AND record_date >= {$last_time}")->getResult();
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
        return Query::table('sb_user_visit')->where('visit_id',$user_id)->where('visit_time',$last_time,'>=')->groupBy('user_id')->get(['user_id'])->getResult();
    }

    /**
     * 用户对话数据
     * @param int $user_id
     * @param int $last_time
     * @return mixed
     */
    public function getUserChatStatisitcs(int $user_id, int $last_time)
    {
        return Query::table('sb_chat_user_statistics')->where('from_id',$user_id)->orWhere('target_id',$user_id)->andWhere('record_date',$last_time,'>=')->get(['from_id','target_id'])->getResult();
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
     * 符合条件的数据
     * @param $params
     * @param $limit
     * @return mixed
     */
    public function getSupplierData($params,$limit)
    {
        return SupplierDataStatistic::findAll($params,['fields' => ['sds_id','user_id'],'limit' => $limit])->getResult();
    }

    /**
     * 符合条件的数据量
     * @param $params
     * @return mixed
     */
    public function getSupplierCount($params)
    {
        return SupplierDataStatistic::count(['sds_id'],$params)->getResult();
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
        return $queryModel->get()->getResult();
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
     * @return \Swoft\Core\ResultInterface
     */
    public function userStrengthPlus($user_id, $strength_id,$params)
    {
        return Query::table('sb_user_strength')->where('user_id',$user_id)->where('id',$strength_id)->update($params);
    }

    /**
     * @param $user_id
     * @param $order_num
     * @param $total_amount
     * @param $take_time
     * @param $prev_amount
     * @return mixed
     * @throws \Swoft\Db\Exception\MysqlException
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
     * @return \Swoft\Core\ResultInterface
     */
    public function checkStrengthOrderRecord($order_num,$user_id)
    {
        return Query::table('sb_user_strength_order_list')->where('user_id',$user_id)->where('order_num',$order_num)->count('usol_id','scount')->getResult();
    }
}
