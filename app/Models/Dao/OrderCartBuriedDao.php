<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 下午1:31
 */

namespace App\Models\Dao;

use Swoft\Bean\Annotation\Bean;
use App\Models\Entity\OrderCartBuried;
/**
 * 订单埋点数据对象
 * @Bean()
 * @uses OrderCartBuriedDao
 * @author Nihuan
 */
class OrderCartBuriedDao
{

    /**
     * 写入日志记录(批量)
     * @author Nihuan
     * @param array $cart_array
     * @return mixed
     */
    public function saveOrderCartBuried(array $cart_array)
    {
        return OrderCartBuried::batchInsert($cart_array)->getResult();
    }
}