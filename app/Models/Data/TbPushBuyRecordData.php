<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Data;

use App\Models\Dao\TbPushBuyRecordDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      TbPushBuyRecordData
 * @author    Nihuan
 */
class TbPushBuyRecordData
{

    /**
     * @Inject()
     * @var TbPushBuyRecordDao
     */
    protected $PushBuyRecordDao;

    /**
     * 获取采购信息
     * @author Nihuan
     * @param int $buyId
     * @return mixed
     */
    public function getPushListById($buyId)
    {
        $fields = ['user_id'];
        return $this->PushBuyRecordDao->getPushRecord($buyId,0, $fields);
    }

    /**
     * 获取单条推荐信息
     * @param $params
     * @return \Swoft\Core\ResultInterface
     */
    public function getUserPushRecord($params)
    {
        $fields = ['id'];
        return $this->PushBuyRecordDao->getRecordByParams($params,$fields);
    }

    /**
     * 更新数据
     * @param $id
     * @param $data
     * @return \Swoft\Core\ResultInterface
     */
    public function updatePushRecord($id,$data)
    {
        return $this->PushBuyRecordDao->updateRecordById($id,$data);
    }

    /**
     * 插入数据
     * @param array $data
     * @return mixed
     */
    public function insertPushRecord(array $data)
    {
        return $this->PushBuyRecordDao->insertRecord($data);
    }
}