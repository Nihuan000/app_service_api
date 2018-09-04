<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-11
 * Time: 下午2:04
 */

namespace App\Pool\Config;


use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Value;

/**
 * the config of service user
 *
 * @Bean()
 */
class ElasticsearchPoolConfig
{

    /**
     * the name of pool
     *
     * @Value(name="${config.elasticsearch.elastic.name}", env="${ES_CLUSTER_NAME}")
     * @var string
     */
    protected $name = '';


    /**
     * the addresses of connection
     *
     * <pre>
     * [
     *  '127.0.0.1:9200',
     * ]
     * </pre>
     *
     * @Value(name="${config.elasticsearch.elastic.uri}", env="${ES_CLUSTER_HOSTS}")
     * @var array
     */
    protected $uri = [];


    /**
     * Connection timeout
     * @Value(name="${config.elasticsearch.elastic.timeout}", env="${ES_WAIT_TIME}")
     * @var int
     */
    protected $timeout = 3;


    /**
     * buy search master
     * @Value(env="${ES_BUY_MASTER}")
     * @var string
     */
    protected $buy_master;


    /**
     * index settings
     * @Value(name="${config.settings}")
     * @var string
     */
    protected $setting;

    /**
     * @Value(env="${ES_PAGE_SIZE}")
     * @var int
     */
    protected $page_size = 20;


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getUri(): array
    {
        return $this->uri;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return array
     */
    public function getSetting(): array
    {
        return json_decode($this->setting,true);
    }

    /**
     * @return string
     */
    public function getBuyMaster() : string
    {
        return $this->buy_master;
    }
}