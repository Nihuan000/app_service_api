<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-22
 * Time: 上午11:03
 */

namespace App\Models\Data;

use App\Models\Dao\OtherDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      OtherData
 * @author    Nihuan
 */
class OtherData
{
    /**
     * @Inject()
     * @var OtherDao
     */
    private $otherDao;

    /**
     * @param $data
     * @return mixed
     */
    public function saveRecords($data)
    {
        return $this->otherDao->ActivateSmsBatch($data);
    }

    /**
     * 召回用户获取
     * @param array $params
     * @param array $field
     * @return array
     */
    public function getUserRecords(array $params,array $field)
    {
        return $this->otherDao->ActivateSmsRecord($params,$field);
    }
}