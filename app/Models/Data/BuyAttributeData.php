<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Data;

use App\Models\Dao\BuyAttributeDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      BuyAttributeData
 * @author    Nihuan
 */
class BuyAttributeData
{

    /**
     * @Inject()
     * @var BuyAttributeDao
     */
    protected $buyAttrDao;

    /**
     * 获取采购信息
     * @author Nihuan
     * @param int $bid
     * @return mixed
     */
    public function getByBid(int $bid)
    {
        return $this->buyAttrDao->findByBid($bid);
    }

}