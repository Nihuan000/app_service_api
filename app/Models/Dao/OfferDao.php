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
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
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
     * @throws MysqlException
     */
    public function setOfferInfo(array $params)
    {
        return Query::table(Offer::class)->insert($params)->getResult();
    }

    /**
     * 报价产品写入
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function setOfferPro(array $data)
    {
        return Query::table('sb_offer_relation_product')->insert($data)->getResult();
    }

    /**
     * 获取报价用户列表
     * @param int $start_time
     * @param int $end_time
     * @param int $limit
     * @return mixed
     * @throws DbException
     */
    public function getOfferUserByCount(int $start_time,int $end_time, int $limit)
    {
        return Db::query("SELECT offerer_id,count(offer_id) as offer_count FROM sb_offer WHERE offer_time BETWEEN {$start_time} AND {$end_time} GROUP BY offerer_id ORDER BY count(offer_id) DESC LIMIT {$limit}")->getResult();
    }

    /**
     * 获取用户收到的报价(指定字段)列表
     * @param int $buyer_id
     * @param array $fields
     * @return mixed
     */
    public function getUserReceiveOfferList(int $buyer_id, $fields = ['*'])
    {
        return Offer::findAll(['user_id' => $buyer_id],['fields' => $fields])->getResult();
    }
}
