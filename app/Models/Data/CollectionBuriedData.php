<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-22
 * Time: 上午11:03
 */

namespace App\Models\Data;

use App\Models\Dao\CollectionBuriedDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      CollectionBuriedData
 * @author    Nihuan
 */
class CollectionBuriedData
{

    /**
     *埋点保存
     * @Inject()
     * @var CollectionBuriedDao
     */
    private $CollectionBuriedDao;

    public function saveCollectionBuried($collection)
    {
        $collect_type = 0;
        $public_id = 0;
        switch ($collection['event'][4]){
            case 'CollectionProduct':
                $collect_type = 1;
                $public_id = $collection['properties']['ProductId'];
                break;

            case 'CollectionBuy':
                $collect_type = 2;
                $public_id = $collection['properties']['BuyId'];
                break;
        }
        $data = [
            'user_id' => $collection['user_id'],
            'collect_type' => $collect_type,
            'public_id' => $public_id,
            'collect_status' => $collection['properties']['CollectStatus'],
            'record_time' => time()
        ];
        return $this->CollectionBuriedDao->saveCollectionBuried($data);
    }
}