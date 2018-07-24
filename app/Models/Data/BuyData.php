<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Data;

use App\Models\Dao\BuyDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      BuyData
 * @author    Nihuan
 */
class BuyData
{

    /**
     * @Inject()
     * @var BuyDao
     */
    protected $buyDao;

    /**
     * 获取采购信息
     * @author Nihuan
     * @param int $bid
     * @return mixed
     */
    public function getBuyInfo(int $bid)
    {
        return $this->buyDao->findById($bid);
    }

}