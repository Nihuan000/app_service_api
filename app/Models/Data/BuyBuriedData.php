<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 下午2:40
 */

namespace App\Models\Data;

use App\Models\Dao\BuyBuriedDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      BuyBuriedData
 * @author    Nihuan
 */
class BuyBuriedData
{

    /**
     * @Inject()
     * @var BuyBuriedDao
     */
    private $buyBuriedDao;

    public function saveBuyBuried($buy_event)
    {
        $buy_status = 1;
        $find_status = 1;
        $offer_id = 0;
        switch ($buy_event['event'][3]){
            case 'PublishBuy':
                $buy_status = 1;
                break;

            case 'UpdateBuy':
                $buy_status =2;
                break;

            case 'DelBuy':
                $buy_status = 4;
                break;

            case 'Audit':
                $buy_status = 5;
                break;

            case 'RefreshBuy':
                $buy_status = 6;
                break;

            case 'UpdateOverBuyInfo':
                $buy_status = 8;
                if($buy_event['properties']['OfferId']){
                   $offer_id = $buy_event['properties']['OfferId'];
                }
                if($buy_event['properties']['FindType']){
                    $find_status = $buy_event['properties']['FindType'];
                }
                break;

            case 'RefreshTask':
                $buy_status = 9;
                break;

            case 'KeepLooking':
                $buy_status = 3;
                if($buy_event['properties']['FindStatus'] == 0){
                    $find_status = 7;
                }else{
                    if($buy_event['properties']['FindType'] == 1){
                        $find_status = 2;
                    }elseif($buy_event['properties']['FindType'] == 2){
                        $find_status = 4;
                    }elseif($buy_event['properties']['FindType'] == 3){
                        $find_status = 7;
                    }elseif ($buy_event['properties']['FindType'] == 4){
                        $find_status = 6;
                    }
                }
                break;
        }
        $data = [
            'buy_id' => (int)$buy_event['properties']['BuyId'],
            'buy_status' => $buy_status,
            'find_status' => $find_status,
            'operation_time' => $buy_event['properties']['OperationTime'],
            'offer_id' => $offer_id,
            'record_time' => time()
        ];
        return $this->buyBuriedDao->saveBuyBuried($data);
    }

    /**
     * 单独的采购可视状态记录
     * @param $buy_id
     * @param $status
     * @param $operation_time
     * @return mixed
     */
    public function buy_viewed_status($buy_id, $status,$operation_time)
    {
        $data = [
            'buy_id' => (int)$buy_id,
            'buy_status' => $status,
            'find_status' => 1,
            'operation_time' => $operation_time,
            'offer_id' => 0,
            'record_time' => time()
        ];
        return $this->buyBuriedDao->saveBuyBuried($data);
    }

    /**
     * 采购变更日志条数
     * @param int $bid
     * @param int $buy_status
     * @return mixed
     */
    public function get_buried_count(int $bid, int $buy_status)
    {
        return $this->buyBuriedDao->getBuriedCount($bid,$buy_status);
    }
}
