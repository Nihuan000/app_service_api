<?php
namespace App\Models\Dao;

use App\Models\Entity\UserRecallRecord;
use Swoft\Bean\Annotation\Bean;

/**
 * 召回记录
 * @Bean()
 * @uses UserRecallRecordDao
 * @author yang
 */
class UserRecallRecordDao
{

    /**
     * 召回记录数
     * @param array $params
     * @return mixed
     */
    public function getRecallCount(array $params)
    {
        return UserRecallRecord::count('urr_id',$params)->getResult();
    }

    /**
     * 批量获取
     * @author yang
     * @param array $params
     * @param array $fields
     * @return mixed
     */
    public function getRecallList(array $params, array $fields)
    {
        return UserRecallRecord::findAll($params, ['fields' => $fields, 'orderBy' => ['urr_id' => 'ASC']])->getResult();
    }

    /**
     * 单条数据
     * @author yang
     * @param array $params
     * @param array $fields
     * @return mixed
     */
    public function getRecallInfoOne(array $params, array $fields)
    {
        return UserRecallRecord::findOne($params, ['fields' => $fields])->getResult();
    }

    /**
     * 召回记录写入
     * @param array $data
     * @return mixed
     */
    public function recordSave(array $data)
    {
        return UserRecallRecord::batchInsert($data)->getResult();
    }

    /**
     * 更新召回数据
     * @param int $id
     * @param array $params
     * @return mixed
     */
    public function updateRecallInfo(int $id, array $params)
    {
        return UserRecallRecord::updateOne($params,['urr_id' => $id])->getResult();
    }
}
