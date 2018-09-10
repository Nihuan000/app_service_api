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
use Swoft\Db\Db;

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
     * @param array $condition
     * @param array $data
     * @return \Swoft\Core\ResultInterface
     */
    public function updateById(array $condition, array $data){
        return Buy::updateOne($data,$condition);
    }

    /**
     * @author Nihuan
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getNoQuoteBuyDao()
    {
        $day_time = date('Y-m-d',strtotime('-1 day'));
        return Db::query("SELECT b.buy_id AS buyId, b.remark, b.pic, b.amount, b.unit FROM sb_buy b LEFT JOIN sb_buy_attribute AS a ON b.buy_id = a.buy_id WHERE b.status = 0 AND  b.del_status = 1 AND  b.is_audit = 0 AND  FROM_UNIXTIME(b.audit_time,'%Y-%m-%d') = '{$day_time}' AND  b.is_find = 0 AND  a.offer_count = 0")->getResult();
    }
}