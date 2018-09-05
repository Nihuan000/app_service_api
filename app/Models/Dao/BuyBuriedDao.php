<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 下午2:41
 */

namespace App\Models\Dao;


use Swoft\Bean\Annotation\Bean;
use App\Models\Entity\BuyBuried;

/**
 * 订单埋点数据对象
 * @Bean()
 * @uses BuyBuriedDao
 * @author Nihuan
 */
class BuyBuriedDao
{

    /**
     * 写入日志记录
     * @author Nihuan
     * @param array $buy
     * @return mixed
     */
    public function saveBuyBuried(array $buy)
    {
        $buried = new BuyBuried();
        $buried->setOperationTime($buy['operation_time']);
        $buried->setRecordTime($buy['record_time']);
        $buried->setBuyId($buy['buy_id']);
        $buried->setBuyStatus($buy['buy_status']);
        $buried->setFindStatus($buy['find_status']);
        $buried->setOfferId($buy['offer_id']);
        return $buried->save()->getResult();
    }
}