<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-22
 * Time: 上午11:03
 */

namespace App\Models\Data;

use App\Models\Dao\OfferDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      OfferData
 * @author    Nihuan
 */
class OfferData
{
    /**
     * @Inject()
     * @var OfferDao
     */
    private $offerDao;

    /**
     * @param $data
     * @return mixed
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function saveOffer($data)
    {
        return $this->offerDao->setOfferInfo($data);
    }

    /**
     * @param $data
     * @return mixed
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function saveOfferProduct($data)
    {
        return $this->offerDao->setOfferPro($data);
    }
}