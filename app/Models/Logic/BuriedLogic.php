<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-21
 * Time: 下午5:43
 */

namespace App\Models\Logic;


use App\Models\Data\OfferBuriedData;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Bean;
use App\Models\Data\CollectionBuriedData;
use App\Models\Data\OrderBuriedData;
use App\Models\Data\OrderCartBuriedData;
use App\Models\Data\BuyBuriedData;
/**
 * 埋点逻辑层
 * @Bean()
 * @uses  BuriedLogic
 * @author Nihuan
 * @package App\Models\Logic
 */
class BuriedLogic
{

    /**
     * 收藏日志
     * @Inject()
     * @var CollectionBuriedData
     */
    private $collectionData;

    /**
     * 订单日志
     * @Inject()
     * @var OrderBuriedData
     */
    private $orderBuriedData;


    /**
     * 购物车日志
     * @Inject()
     * @var OrderCartBuriedData
     */
    private $orderCartBuriedData;

    /**
     * 报价日志
     * @Inject()
     * @var OfferBuriedData
     */
    private $offerBuriedData;

    /**
     * 采购日志
     * @Inject()
     * @var BuyBuriedData
     */
    private $buyBuriedData;

    public function event_analysis(array $event)
    {
        $buriedData = false;
        switch ($event['event'][2]){
            case 'Collection':
                $buriedData = $this->collectionData->saveCollectionBuried($event);
                break;

            case 'Order':
            case 'OrderInfo':
            case 'OrderPay':
            case 'OrderProcess':
                $buriedData = $this->orderBuriedData->saveOrderBuried($event);
                break;

            case 'OrderCart':
                $buriedData = $this->orderCartBuriedData->saveOrderCartBuried($event);
                break;

            case 'Buy':
            case 'AuditBuy':
            case 'TaskBuy':
                $buriedData = $this->buyBuriedData->saveBuyBuried($event);
                break;

            case 'Offer':
                $buriedData = $this->offerBuriedData->saveBuyOfferBuried($event);
        }
        return $buriedData;
    }

    /**
     * 可查看状态记录
     * @param array $event
     * @return mixed
     */
    public function buy_buried(array $event)
    {
        $buy_id = (int)$event['buy_id'];
        $status = (int)$event['buy_status'];
        $operation_time = $event['time'];
        return $this->buyBuriedData->buy_viewed_status($buy_id,$status,$operation_time);
    }

    /**
     * 记录获取
     * @param int $id
     * @param int $status
     * @return mixed
     */
    public function get_buried_count(int $id, int $status)
    {
        return $this->buyBuriedData->get_buried_count($id, $status);
    }
}
