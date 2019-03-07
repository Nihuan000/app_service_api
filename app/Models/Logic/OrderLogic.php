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
            write_log(3,"订单{$order_info['orderNum']}转账记录不存在");
            return ;
        }
        //支付类型判断
        if($order_info['payType'] != 6){
            write_log(3,"订单{$order_info['orderNum']}支付类型不符合");
            return ;
        }
        //确认收货时间判断
        if($order_info['takeTime'] > $end_time || $order_info['takeTime'] < $start_time){
            write_log(3,"订单{$order_info['orderNum']}确认收货时间{$order_info['takeTime']}不符合");
            return ;
        }
        //用户当前余额
        $user_balance = $this->orderData->getUserBalance($order_info['buyerId']);
        $balance_json = json_encode($user_balance);
        write_log(3,"用户{$order_info['buyerId']}钱包余额信息:{$balance_json}");
        //检测是否已返现
        $return_exists = $this->redis->sIsmember($return_cash_history,$order_info['orderNum']);
        if($return_exists){
            write_log(3,"订单{$order_info['orderNum']}返现记录已存在");
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
        }else{
            write_log(3,"订单{$order_info['orderNum']}返现处理失败");
        }
    }

}
