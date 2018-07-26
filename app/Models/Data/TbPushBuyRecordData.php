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

}