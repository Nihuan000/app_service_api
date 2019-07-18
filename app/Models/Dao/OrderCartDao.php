<?php
/**
 * @author yang
 * @date 19-7-18
 */

namespace App\Models\Dao;

use App\Models\Entity\Order;
use Swoft\Bean\Annotation\Bean;
use App\Models\Entity\OrderCart;
/**
 * 购物车相关
 * @Bean()
 * @uses OrderCartDao
 * @author yang
 */
class OrderCartDao
{

    /**
     * 获取购物车信息
     * @author yang
     * @date 19-7-18
     * @param array
     * @return array
     */
    public function getOrderList(array $params,array $fields)
    {
        return OrderCart::findAll($params,['fields' => $fields])->getResult();
    }
}