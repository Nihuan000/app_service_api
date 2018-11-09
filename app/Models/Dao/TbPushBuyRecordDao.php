<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Dao;

use App\Models\Entity\TbPushBuyRecord;
use Swoft\Bean\Annotation\Bean;
use Swoft\Db\Query;

/**
 * 采购数据对象
 * @Bean()
 * @uses TbPushBuyRecordDao
 * @author Nihuan
 */
class TbPushBuyRecordDao
{
    /**
     * @author Nihuan
     * @param int $bid 采购id
     * @param int $status -1: 全部 0:未读 1:已读
     * @param array $column 获取字段
     * @return \Swoft\Core\ResultInterface
     */
    public function getPushRecord(int $bid, int $status, array $column = ['*'])
    {
        return Query::table(TbPushBuyRecord::class)
            ->selectInstance('search')
            ->where('is_read',$status)
            ->where('buy_id',$bid)
            ->get($column)
            ->getResult();
    }

    /**
     * 获取与我相关信息
     * @param array $params
     * @param array $column
     * @return \Swoft\Core\ResultInterface
     */
    public function getRecordByParams(array $params, array $column = ['*'])
    {
        $user_id = $params['user_id'];
        $buy_id = $params['buy_id'];
        return Query::table(TbPushBuyRecord::class)
            ->selectInstance('search')
            ->where('user_id',$user_id)
            ->where('buy_id',$buy_id)
            ->one($column)
            ->getResult();
    }

    /**
     * 记录信息修改
     * @param int $id
     * @param array $data
     * @return \Swoft\Core\ResultInterface
     */
    public function updateRecordById(int $id, array $data)
    {
        return Query::table(TbPushBuyRecord::class)
            ->selectInstance('search')
            ->where('id',$id)
            ->update($data)
            ->getResult();
    }

    /**
     * 推荐记录写入
     * @param array $data
     * @return mixed
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function insertRecord(array $data)
    {
        $data['day_time'] = strtotime(date('Y-m-d'));
        $data['uuid'] = '@Tag_' . create_guid();
        $data['update_time'] = (int)microtime(1) * 1000;
        return Query::table(TbPushBuyRecord::class)
            ->selectInstance('search')
            ->insert($data)
            ->getResult();
    }
}