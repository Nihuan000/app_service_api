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
        return $this->orderBuriedDao->saveOrderBuried($data);

    }

}