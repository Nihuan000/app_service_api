<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 下午5:41
 */

namespace App\Models\Data;

use App\Models\Dao\OrderDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      OrderData
 * @author    Nihuan
 */
class OrderData
{

    /**
     * 订单数据对象
     * @Inject()
     * @var OrderDao
     */
    private $orderDao;


    /**
     * 根据关键词获取订单列表
     * @author Nihuan
     * @param $keyword
     * @return array
     */
    public function getOrderWithKeyword($keyword)
    {
        return $this->orderDao->getOrderWithKeyword($keyword);
    }
}