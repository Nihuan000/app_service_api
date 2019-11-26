<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 下午2:41
 */

namespace App\Models\Dao;


use App\Models\Entity\BuySearchLog;
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

    /**
     * 搜索日志添加
     * @param array $buy
     * @return mixed
     */
    public function saveSearchBuried(array $buy)
    {
        $buried = new BuySearchLog();
        $buried->setUserId($buy['user_id']);
        $buried->setParentid($buy['parentid']);
        $buried->setPageNum($buy['page_num']);
        $buried->setIsHot($buy['is_hot']);
        $buried->setLabelIds($buy['label_ids']);
        $buried->setAppVersion($buy['version']);
        $buried->setSearchTime(time());
        $buried->setRequestId($buy['request_id']);
        $buried->setMatchIds($buy['match_ids']);
        $buried->setMatchNum($buy['match_num']);
        return $buried->save()->getResult();
    }

    /**
     * 日志条数
     * @param int $bid
     * @param int $status
     * @return mixed
     */
    public function getBuriedCount(int $bid, int $status)
    {
        return BuyBuried::count('*',['buy_id' => $bid, 'buy_status' => $status])->getResult();
    }
}
