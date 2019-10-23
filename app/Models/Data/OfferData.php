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
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;

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
     * @throws MysqlException
     */
    public function saveOffer($data)
    {
        return $this->offerDao->setOfferInfo($data);
    }

    /**
     * @param $data
     * @return mixed
     * @throws MysqlException
     */
    public function saveOfferProduct($data)
    {
        return $this->offerDao->setOfferPro($data);
    }

    /**
     * 最高报价数列表
     * @param int $start_time
     * @param int $end_time
     * @param int $limit
     * @return mixed
     * @throws DbException
     */
    public function getOffererListByTime(int $start_time,int $end_time, int $limit = 100)
    {
        return $this->offerDao->getOfferUserByCount($start_time, $end_time, $limit);
    }
}
