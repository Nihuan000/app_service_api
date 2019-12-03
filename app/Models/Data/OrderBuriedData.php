<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 上午11:37
 */

namespace App\Models\Data;

use App\Models\Dao\OrderBuriedDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;

/**
 *
 * @Bean()
 * @uses      OrderBuriedData
 * @author    Nihuan
 */
class OrderBuriedData
{

    /**
     *埋点保存
     * @Inject()
     * @var OrderBuriedDao
     */
    private $orderBuriedDao;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @Inject()
     * @var OrderData
     */
    private $orderData;

    /**
     * @Inject("appRedis")
     * @var Redis
     */
    private $redis;

    public function saveOrderBuried($order)
    {
        $order_status = 0;
        if(isset($order['properties']['OrderStatus'])){
            $order_status = (int)$order['properties']['OrderStatus'];
        }else{
            switch ($order['event'][3]){
                case 'PostOrder':
                    $order_status = 1;
                    break;

                case 'PayForOrder':
                case 'PayForAli':
                case 'PayForWCPay':
                case 'PayForPublicTransfer':
                $order_status = 2;
                    break;

                case 'ChangePrice':
                    $order_status = 3;
                    break;

                case 'CancelOrder':
                    $order_status = 4;
                    break;

                case 'SetRefund':
                    $order_status = 5;
                    break;

                case 'SetComplaint':
                    $order_status = 6;
                    break;

                case 'SellerShipped':
                    $order_status = 7;
                    break;

                case 'TakeOrder':
                    $order_status = 8;
                    break;

                case 'DeleteOrder':
                    $order_status = 9;
                    break;

                case 'FastArrival':
                    $order_status = 10;
                    break;

                case 'SellerCloseOrder':
                    $order_status = 11;
                    break;

                case 'SellerOrder':
                    $order_status = 12;
                    break;

                case 'ConfirmOrder':
                    $order_status = 13;
                    break;
            }
        }
        $data = [
            'order_num' => $order['properties']['OrderNum'],
            'order_status' => (int)$order_status,
            'operation_time' => $order['properties']['OperationTime'],
            'current_status' => $order['properties']['Status'],
            'current_sec_status' => $order['properties']['SecStatus'],
            'updated_price' => $order['properties']['CurrentPrice'],
            'record_time' => time()
        ];

        $result = $this->orderBuriedDao->saveOrderBuried($data);
        return $result;
    }

    /**
     * 交易服务通知
     * @param $order_num
     * @param $order_status
     */
    public function send_service_tips($order_num, $order_status)
    {
        $week = date('w');
        $now_time = date('H');
        if(in_array($week,[0,6]) || $now_time > 18 || $now_time < 9){
            write_log(3,'时间不符合');
            return;
        }
        $send_history_key = 'service_tips_' . date('Y_m_d');
        $send_history = $this->redis->has($send_history_key);
        $orderInfo = $this->orderData->getOrderInfo($order_num,['buyer_id']);
        if(!empty($orderInfo)){
            $no_receive_list = $this->userData->getSetting('feedback_user_ids');
            if(in_array($orderInfo['buyerId'],$no_receive_list)){
                write_log(3,'用户已反馈');
                return;
            }
            if($this->redis->hGet($send_history_key,$orderInfo['buyerId'])){
                write_log(3,'消息已发送');
                return;
            }
            $msg = '';
            $service_id = 0;
            switch ($order_status){
                case 2:
                    $service_ids = $this->userData->getSetting('paid_service_ids');
                    if(is_array($service_ids) && !empty($service_ids)){
                        $key = array_rand($service_ids);
                        $service_id = (int)$service_ids[$key];
                    }
                    if($service_id > 0){
                        $msg = "您好，最近搜布做快递补贴活动，顺丰3kg最低只要10元，您这边需要寄快递吗？";
                    }
                    break;
            }

            if(!empty($msg) && $service_id > 0 && $service_id != $orderInfo['buyerId']){
                sendC2CMessaging((string)$service_id,$orderInfo['buyerId'],$msg);
                $this->redis->hSet($send_history_key,$orderInfo['buyerId'],time());
                if(!$send_history){
                    $this->redis->expire($send_history_key,7*24*3600);
                }
            }
        }
    }
}
