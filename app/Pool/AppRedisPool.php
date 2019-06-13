<?php
/**
 * This file is part of Swoft.
 *
 * @link    https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Pool;

use App\Pool\Config\AppRedisPoolConfig;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Pool;
use Swoft\Redis\Pool\RedisPool;

/**
 * DemoRedisPool
 *
 * @Pool("appRedis")
 */
class AppRedisPool extends RedisPool
{
    /**
     * @Inject()
     * @var AppRedisPoolConfig
     */
    public $poolConfig;
}