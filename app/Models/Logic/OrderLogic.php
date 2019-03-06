<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Logic;

use App\Models\Data\OrderData;
use App\Models\Data\UserData;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;

/**
 *
 * @Bean()
 * @uses      OrderLogic
 */
class OrderLogic
{
    /**
     * @Inject()
     * @var OrderData
     */
    private $orderData;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    public function get_order_info($order_num,$fields = [])
    {
        return $this->orderData->getOrderInfo($order_num,$fields);
    }

    /**
     * @param $order_info
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function return_cash_action($order_info)
    {
        $return_cash_history = 'return_cash_member';
        $now_time = time();
        //是否在活动期间
        $start_time = $this->userData->getSetting('public_pay_start_time');
        $end_time = $this->userData->getSetting('public_pay_end_time');
        if($now_time > $end_time || $now_time < $start_time){
            return ;
        }
        //验证对公转账数据
        $transfer_info = $this->orderData->getPublicTransfer($order_info['orderNum']);
        if(empty($transfer_info)){
            return ;
        }
        //检测是否已返现
        $return_exists = $this->redis->sIsmember($return_cash_history,$order_info['orderNum']);
        if($return_exists){
            return ;
        }

        $returnRes = $this->orderData->returnCashBack($order_info);
        if($returnRes){
            //写入已返现缓存
            $this->redis->sAdd($return_cash_history,$order_info['orderNum']);
            //发送系统消息
            $res['extra']['title'] = '返现成功';
            $res['extra']['content'] = '交易金额10000元以上订单使用公司转账付款，已成功返现50元至搜布钱包';
            $res['extra']['msgContent'] = '交易金额10000元以上订单使用公司转账付款，已成功返现50元至搜布钱包';
            sendInstantMessaging('1', (string)$order_info['buyerId'], json_encode($res['extra']));
        }
    }

}
