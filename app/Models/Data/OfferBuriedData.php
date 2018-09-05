<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-22
 * Time: 上午11:03
 */

namespace App\Models\Data;

use App\Models\Dao\BuyBuriedDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      OfferBuriedData
 * @author    Nihuan
 */
class OfferBuriedData
{

    /**
     * @Inject()
     * @var BuyBuriedDao
     */
    private $buyBuriedDao;

    public function saveBuyOfferBuried($offer)
    {
        $buy_status = 1;
        $find_status = 1;
        $offer_id = 0;
        switch ($offer['event'][3]){
            case 'Offer':
                $buy_status = 7;
                $offer_id = $offer['properties']['OfferId'];
                break;
        }
        $data = [
            'buy_id' => (int)$offer['properties']['BuyId'],
            'buy_status' => $buy_status,
            'find_status' => $find_status,
            'operation_time' => $offer['properties']['OperationTime'],
            'offer_id' => $offer_id,
            'record_time' => time()
        ];
        return $this->buyBuriedDao->saveBuyBuried($data);
    }
}