<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Data;

use App\Models\Dao\UserRecallRecordDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 * 用户召回记录数据类
 * @Bean()
 * @uses      UserRecallRecordData
 */
class UserRecallRecordData
{

    /**
     * @Inject()
     * @var UserRecallRecordDao
     */
    private $recallDao;

    /**
     * 召回记录数
     * @param array $params
     * @return mixed
     */
    public function get_recall_count(array $params)
    {
        return $this->recallDao->getRecallCount($params);
    }

    /**
     * 召回列表获取
     * @param array $params
     * @param array $fields
     * @return mixed
     */
    public function get_recall_list(array $params, $fields = ['*'])
    {
        return $this->recallDao->getRecallList($params,$fields);
    }

    /**
     * 召回记录批量写入
     * @param array $data
     * @return mixed
     */
    public function batch_record_save(array $data)
    {
        return $this->recallDao->recordSave($data);
    }

    /**
     * 更新数据
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update_recall_info(int $id, array $data)
    {
        return $this->recallDao->updateRecallInfo($id,$data);
    }
}
