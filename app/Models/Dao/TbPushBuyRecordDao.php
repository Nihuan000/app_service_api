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
}