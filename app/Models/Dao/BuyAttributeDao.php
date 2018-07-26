<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Dao;

use App\Models\Entity\BuyAttribute;
use Swoft\Bean\Annotation\Bean;

/**
 * 采购扩展信息数据对象
 * @Bean()
 * @uses BuyAttributeDao
 * @author Nihuan
 */
class BuyAttributeDao
{
    /**
     * 查询采购扩展数据
     * @author Nihuan
     * @param int $id
     * @return mixed
     */
    public function findByBid(int $id)
    {
        return BuyAttribute::findOne(['buy_id' => $id])->getResult();
    }
}