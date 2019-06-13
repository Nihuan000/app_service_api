<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Pool\Config;

use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Value;
use Swoft\Redis\Pool\Config\RedisPoolConfig;

/**
 * AppRedisPoolConfig
 *
 * @Bean()
 */
class AppRedisPoolConfig extends RedisPoolConfig
{
    /**
     * @Value(name="${config.cache.appRedis.db}", env="${APP_REDIS_DB}")
     * @var int
     */
    protected $db = 0;

    /**
     * @Value(name="${config.cache.appRedis.prefix}", env="${APP_REDIS_PREFIX}")
     * @var string
     */
    protected $prefix = '';

    /**
     * @Value(env="${APP_REDIS_SERIALIZE}")
     * @var int
     */
    protected $serialize = 0;

    /**
     * @return int
     */
    public function getSerialize(): int
    {
        return $this->serialize;
    }
}