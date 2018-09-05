<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-9-4
 * Time: 下午3:43
 */

namespace App\Models\Data;

use App\Models\Dao\BuyFfectiveLogDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      BuyAttributeData
 * @author    Nihuan
 */
class BuyFfectiveLogData
{
    /**
     * @Inject()
     * @var BuyFfectiveLogDao
     */
    protected $buyFfectDao;

    /**
     * 获取采购信息
     * @author Nihuan
     * @param int $bid
     * @param int $type 获取状态 0:待执行 1：已执行
     * @return mixed
     */
    public function getByBid(int $bid, int $type = 0)
    {
        return $this->buyFfectDao->findByBid($bid, $type);
    }
}