<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 19-7-18
 */

namespace App\Models\Data;

use App\Models\Dao\OrderCartDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      OrderCart
 * @author    yang
 */
class OrderCartData
{

    /**
     * @Inject()
     * @var OrderCartDao
     */
    private $orderCart;

    /**
     * 获取购物车信息
     * @param $params
     * @param $fields
     * @return array
     */
    public function getList(array $params,array $fields)
    {
        return $this->orderCart->getOrderList($params, $fields);
    }
}