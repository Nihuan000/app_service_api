<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 下午5:41
 */

namespace App\Models\Dao;

use App\Models\Entity\ActivateSms;
use Swoft\Bean\Annotation\Bean;
use Swoft\Db\Query;

/**
 * 订单数据对象
 * @Bean()
 * @uses OtherDao
 * @author Nihuan
 */
class OtherDao
{
    /**
     * 短信发送记录批量写入
     * @param array $data
     * @return mixed
     */
    public function ActivateSmsBatch(array $data)
    {
        return ActivateSms::batchInsert($data)->getResult();
    }

    /**
     * 发送记录获取
     * @param array $params
     * @param array $field
     * @return mixed
     */
    public function ActivateSmsRecord(array $params, array $field)
    {
        return ActivateSms::findAll($params,['fields' => $field])->getResult();
    }
}