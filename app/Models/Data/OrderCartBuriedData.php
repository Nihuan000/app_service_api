<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 下午1:31
 */

namespace App\Models\Data;

use App\Models\Dao\OrderCartBuriedDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      OrderCartBuriedData
 * @author    Nihuan
 */
class OrderCartBuriedData
{

    /**
     * @Inject()
     * @var OrderCartBuriedDao
     */
    private $orderCartBuriedDao;

    public function saveOrderCartBuried($cart_event)
    {
        $data = [];
        $cart_status = 0;
        switch ($cart_event['event'][3]){
            case 'PutToCart':
                $cart_status = 1;
                break;

            case 'PutCartInfo':
                $cart_status = 2;
                break;

            case 'PutToFavorites':
            case 'DeleteCartPro':
            case 'DeleteInvalidPro':
                $cart_status = 3;
                break;

            case 'SubmitCartOrder':
                $cart_status = 2;
                break;

            case 'GetCartInfo':
                $cart_status = 4;
                break;
        }
        $operation_time = $cart_event['properties']['OperationTime'];
        unset($cart_event['properties']['OperationTime']);
        foreach ($cart_event['properties'] as $property) {
            $data[] = [
                'user_id' => $property['UserId'],
                'pro_id' => $property['ProId'],
                'order_type' => $property['OrderType'],
                'amount' => is_null($property['Amount']) ? 0 : $property['Amount'],
                'status' => $cart_status,
                'operation_time' => $operation_time
            ];
        }
        return $this->orderCartBuriedDao->saveOrderCartBuried($data);
    }
}