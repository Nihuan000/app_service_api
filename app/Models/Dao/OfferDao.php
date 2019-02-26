<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 下午5:41
 */

namespace App\Models\Dao;

use Swoft\Bean\Annotation\Bean;
use App\Models\Entity\Offer;
use Swoft\Db\Query;

/**
 * 订单数据对象
 * @Bean()
 * @uses OfferDao
 * @author Nihuan
 */
class OfferDao
{
    /**
     * 获取用户报价的采购id列表
     * @param array $params
     * @return mixed
     */
    public function getUserOfferBid(array $params)
    {
        return Offer::findAll($params,['fields' => ['buy_id']])->getResult();
    }

    /**
     * 获取符合条件报价数
     * @param array $params
     * @return mixed
     */
    public function getUserOfferCount(array $params)
    {
        return Offer::count('*',$params)->getResult();
    }

    /**
     * 写入报价数据
     * @param array $params
     * @return mixed
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function setOfferInfo(array $params)
    {
        return Query::table(Offer::class)->insert($params)->getResult();
    }

    /**
     * 报价产品写入
     * @param array $data
     * @return mixed
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function setOfferPro(array $data)
    {
        return Query::table('sb_offer_relation_product')->insert($data)->getResult();
    }
}