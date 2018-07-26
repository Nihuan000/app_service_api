<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-26
 * Time: 下午2:52
 */

namespace App\Pool;


use App\Pool\Config\DbSearchPoolConfig;
use Swoft\App;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Pool;
use Swoft\Db\Bean\Collector\ConnectionCollector;
use Swoft\Db\Driver\DriverType;
use Swoft\Db\Exception\DbException;
use Swoft\Pool\ConnectionInterface;

/**
 * Search Db pool
 *
 * @Pool("default.search")
 */
class DbSearchPool
{
    /**
     * The config of search poolbPool
     *
     * @Inject()
     *
     * @var DbSearchPoolConfig
     */
    protected $searchPoolConfig;

    /**
     * Create connection
     *
     * @return ConnectionInterface
     * @throws \Swoft\Db\Exception\DbException
     */
    public function createConnection(): ConnectionInterface
    {
        $driver    = $this->searchPoolConfig->getDriver();
        $collector = ConnectionCollector::getCollector();

        if (App::isCoContext()) {
            $connectClassName = $this->getCorConnectClassName($collector, $driver);
        } else {
            $connectClassName = $this->getSyncConnectClassName($collector, $driver);
        }

        return new $connectClassName($this);
    }

    /**
     * @param array  $collector
     * @param string $driver
     *
     * @return string
     * @throws \Swoft\Db\Exception\DbException
     */
    private function getCorConnectClassName(array $collector, string $driver): string
    {
        if (!isset($collector[$driver][DriverType::COR])) {
            throw new DbException('The coroutine driver of ' . $driver . ' is not exist!');
        }

        return $collector[$driver][DriverType::COR];
    }

    /**
     * @param array  $collector
     * @param string $driver
     *
     * @return string
     * @throws \Swoft\Db\Exception\DbException
     */
    private function getSyncConnectClassName(array $collector, string $driver): string
    {
        if (!isset($collector[$driver][DriverType::SYNC])) {
            throw new DbException('The synchronous driver of ' . $driver . ' is not exist!');
        }

        return $collector[$driver][DriverType::SYNC];
    }
}