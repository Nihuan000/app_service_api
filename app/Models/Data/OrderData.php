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
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;

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

    /**
     * 获取订单信息
     * @param $order_num
     * @param array $fields
     * @return mixed
     */
    public function getOrderInfo($order_num,$fields = ['*'])
    {
        return $this->orderDao->getOrderInfo($order_num,$fields);
    }

    /**
     * @param $order_num
     * @return mixed
     */
    public function getPublicTransfer($order_num)
    {
        return $this->orderDao->getPublicTransferInfo($order_num);
    }

    /**
     * @param $order_info
     * @return bool
     * @throws MysqlException
     * @throws DbException
     */
    public function returnCashBack($order_info)
    {
        return $this->orderDao->returnCashBackToWlt($order_info);
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function getUserBalance($user_id)
    {
        return $this->orderDao->getUserWalletBalance($user_id);
    }

    /**
     * @param int $user_id
     * @return mixed
     */
    public function getOrderAllPrice($user_id)
    {
        return $this->orderDao->getOrderAllPrice($user_id);
    }

    /**
     * 增加订单日志日志
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function addOrderRecord(array $data)
    {
        return $this->orderDao->addOrderRecord($data);
    }
}