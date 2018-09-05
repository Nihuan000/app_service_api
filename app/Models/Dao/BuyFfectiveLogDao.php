<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-9-4
 * Time: 下午3:44
 */

namespace App\Models\Dao;

use App\Models\Entity\BuyFfectiveLog;
use Swoft\Bean\Annotation\Bean;

/**
 * 采购过期信息数据对象
 * @Bean()
 * @uses BuyFfectiveLogDao
 * @author Nihuan
 */
class BuyFfectiveLogDao
{
    /**
     * 查询采购有效期
     * @author Nihuan
     * @param int $id
     * @param int $type
     * @return mixed
     */
    public function findByBid(int $id, int $type)
    {
        return BuyFfectiveLog::findOne(['buy_id' => $id, 'status' => $type])->getResult();
    }
}