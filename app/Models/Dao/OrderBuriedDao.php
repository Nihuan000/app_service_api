<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 上午11:32
 */

namespace App\Models\Dao;

use Swoft\Bean\Annotation\Bean;
use App\Models\Entity\OrderBuried;

/**
 * 订单埋点数据对象
 * @Bean()
 * @uses OrderBuriedDao
 * @author Nihuan
 */
class OrderBuriedDao
{
    /**
     * 写入日志记录
     * @author Nihuan
     * @param array $buried_data
     * @return mixed
     */
    public function saveOrderBuried(array $buried_data)
    {
        $order = new OrderBuried();
        $order->setOrderNum($buried_data['order_num']);
        $order->setOrderStatus($buried_data['order_status']);
        $order->setOrderPrice($buried_data['order_price']);
        $order->setOperationTime($buried_data['operation_time']);
        $order->setCurrentStatus($buried_data['current_status']);
        $order->setCurrentSecStatus($buried_data['current_sec_status']);
        $order->setUpdatedPrice($buried_data['updated_price']);
        $order->setRecordTime($buried_data['record_time']);
        return $order->save()->getResult();
    }
}