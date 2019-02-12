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
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

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

    public function get_order_info($order_num,$fields = [])
    {
        return $this->orderData->getOrderInfo($order_num,$fields);
    }

}
