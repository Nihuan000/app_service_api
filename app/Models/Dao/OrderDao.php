<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 下午5:41
 */

namespace App\Models\Dao;

use Swoft\Bean\Annotation\Bean;
use App\Models\Entity\Order;

/**
 * 订单数据对象
 * @Bean()
 * @uses OrderDao
 * @author Nihuan
 */
class OrderDao
{

    public function getOrderInfo($order_num,$fields)
    {
        return Order::findOne(['order_num' => $order_num,['fields' => $fields]])->getResult();
    }


    /**
     * 根据产品信息获取
     * @author Nihuan
     * @param string $keyword
     * @return array
     */
    public function getOrderWithKeyword(string $keyword)
    {

        //TODO 根据关键词搜索订单列表
        return [];
    }
}