<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-26
 * Time: 下午2:49
 */

namespace App\Pool\Config;


use Swoft\Bean\Annotation\Value;
use Swoft\Bean\Annotation\Bean;
use Swoft\Db\Driver\Driver;
use Swoft\Db\Pool\Config\DbPoolProperties;

/**
 * Search pool
 *
 * @Bean()
 */
class DbSearchPoolConfig extends DbPoolProperties
{
    /**
     * @Value(name="${config.db.search.name}", env="${DB_SEARCH_NAME}")
     * @var string
     */
    protected $name = '';

    /**
     * @Value(name="${config.db.search.minActive}", env="${DB_SEARCH_MIN_ACTIVE}")
     * @var int
     */
    protected $minActive = 5;

    /**
     * @Value(name="${config.db.search.maxActive}", env="${DB_SEARCH_MAX_ACTIVE}")
     * @var int
     */
    protected $maxActive = 10;

    /**
     * @Value(name="${config.db.search.maxWait}", env="${DB_SEARCH_MAX_WAIT}")
     * @var int
     */
    protected $maxWait = 20;

    /**
     * @Value(name="${config.db.search.maxIdleTime}", env="${DB_SEARCH_MAX_IDLE_TIME}")
     * @var int
     */
    protected $maxIdleTime = 60;

    /**
     * @Value(name="${config.db.search.maxWaitTime}", env="${DB_SEARCH_MAX_WAIT_TIME}")
     * @var int
     */
    protected $maxWaitTime = 3;

    /**
     * @Value(name="${config.db.search.timeout}", env="${DB_SEARCH_TIMEOUT}")
     * @var int
     */
    protected $timeout = 3;

    /**
     * the addresses of connection
     *
     * <pre>
     * [
     *  '127.0.0.1:88',
     *  '127.0.0.1:88'
     * ]
     * </pre>
     *
     * @Value(name="${config.db.search.uri}", env="${DB_SEARCH_URI}")
     * @var array
     */
    protected $uri = [];

    /**
     * whether to user provider(consul/etcd/zookeeper)
     *
     * @Value(name="${config.db.search.useProvider}", env="${DB_SEARCH_USE_PROVIDER}")
     * @var bool
     */
    protected $useProvider = false;

    /**
     * the default balancer is random balancer
     *
     * @Value(name="${config.db.search.balancer}", env="${DB_SEARCH_BALANCER}")
     * @var string
     */
    protected $balancer = '';

    /**
     * the default provider is consul provider
     *
     * @Value(name="${config.db.search.provider}", env="${DB_SEARCH_PROVIDER}")
     * @var string
     */
    protected $provider = '';

    /**
     * the default driver is consul mysql
     *
     * @Value(name="${config.db.search.driver}", env="${DB_SEARCH_DRIVER}")
     * @var string
     */
    protected $driver = Driver::MYSQL;

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }
}