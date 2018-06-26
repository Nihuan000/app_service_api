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
        switch ($buy_event['event'][4]){
            case 'PublishBuy':
                $buy_status = 1;
                break;

            case 'UpdateBuy':
                $buy_status =2;
                break;

            case 'DelBuy':
                $buy_status = 3;
                break;

            case 'KeepLooking':
                $buy_status = 1;
                if($buy_event['properties']['BuyStatus'] == 0){
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
            'record_time' => time()
        ];
        return $this->buyBuriedDao->saveOrderBuried($data);
    }
}