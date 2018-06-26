<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-22
 * Time: 上午10:58
 */

namespace App\Models\Dao;

use App\Models\Entity\CollectionBuried;
use Swoft\Bean\Annotation\Bean;

/**
 * 收藏埋点数据对象
 * @Bean()
 * @uses CollectionBuriedDao
 * @author Nihuan
 */
class CollectionBuriedDao
{

    /**
     * 写入日志记录
     * @author Nihuan
     * @param array $buried_data
     * @return mixed
     */
    public function saveCollectionBuried(array $buried_data)
    {
        $buried = new CollectionBuried();
        $buried->setUserId($buried_data['user_id']);
        $buried->setRecordTime($buried_data['record_time']);
        $buried->setCollectStatus($buried_data['collect_status']);
        $buried->setPublicId($buried_data['public_id']);
        $buried->setCollectType($buried_data['collect_type']);
        return $buried->save()->getResult();
    }
}