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

use App\Models\Dao\UserDao;
use App\Models\Dao\SafePriceDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Core\ResultInterface;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
use Swoft\Redis\Redis;

/**
 * 用户数据类
 * @Bean()
 * @uses      UserData
 * @author    Dailifeng
 */
class SafePriceData
{
    /**
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * @Inject()
     * @var SafePriceDao
     */
    private $SafePriceDao;


    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @author Dailifeng
     * @param $uid
     * @return mixed
     */
    public function getLastLogInfo($uid)
    {
        return $this->SafePriceDao->getLastLogInfo($uid);
    }

    /**
     * @author Dailifeng
     * @param $data
     * @return mixed
     */
    public function addSafePriceLog($data)
    {
        return $this->SafePriceDao->addSafePriceLog($data);
    }
}
