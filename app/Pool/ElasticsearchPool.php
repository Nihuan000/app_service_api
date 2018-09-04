<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 下午2:04
 */

namespace App\Pool;

use App\Pool\Config\ElasticsearchPoolConfig;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Pool;
use Swoft\Exception\PoolException;
use Elasticsearch\ClientBuilder;

/**
 * ElasticsearchPool
 *
 * @Pool("ElasticsearchPool")
 * @method search(array $params)
 */
class ElasticsearchPool
{

    /**
     * @Inject()
     * @var ElasticsearchPoolConfig
     */
    public $poolConfig;

    /**
     * 单连接池
     * @Inject()
     * @author Nihuan
     * @return \Elasticsearch\Client
     * @throws PoolException
     */
    public function simpleConnectionPool()
    {
        if (empty($this->poolConfig)) {
            throw new PoolException('You must to set elasticPoolConfig by @Inject!');
        }
        $client = ClientBuilder::create()
            ->setConnectionPool('\Elasticsearch\ConnectionPool\SimpleConnectionPool',[])
            ->setHosts($this->poolConfig->getUri())->build();
        return $client;
    }

    /**
     * 静态连接池
     * @Inject()
     * @author Nihuan
     * @return \Elasticsearch\Client
     * @throws PoolException
     */
    public function staticNoPingConnectionPool()
    {
        if (empty($this->poolConfig)) {
            throw new PoolException('You must to set elasticPoolConfig by @Inject!');
        }
        $client = ClientBuilder::create()
            ->setConnectionPool('\Elasticsearch\ConnectionPool\staticNoPingConnectionPool',[])
            ->setHosts($this->poolConfig->getUri())->build();
        return $client;
    }
}