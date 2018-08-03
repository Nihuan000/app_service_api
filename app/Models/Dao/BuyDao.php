<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Dao;

use App\Models\Entity\Buy;
use Swoft\Bean\Annotation\Bean;
use Swoft\Db\Query;

/**
 * 采购数据对象
 * @Bean()
 * @uses BuyDao
 * @author Nihuan
 */
class BuyDao
{
    /**
     * 主键查询一条数据
     * @author Nihuan
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return Buy::findById($id)->getResult();
    }

    /**
     * @author Nihuan
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getNoQuoteBuyDao()
    {
        $day_time = date('Y-m-d',strtotime('-1 day'));
        return Query::table(Buy::class)
            ->leftJoin('sb_buy_attribute',['a.buy_id' => 'b.buy_id'],'a')
            ->where('b.status',0)
            ->where('b.del_status',1)
            ->where('b.is_audit',0)
            ->where("FROM_UNIXTIME(b.audit_time,'%Y-%m-%d')",$day_time)
            ->where('b.is_find',0)
            ->where('a.offer_count',0)
            ->get(['b.buy_id','b.remark','b.pic','b.amount','b.unit'])
            ->getResult();
    }
}