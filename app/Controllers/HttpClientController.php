<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Controllers;

use Swoft\Bean\Annotation\Inject;
use Swoft\HttpClient\Client;
use Swoft\Redis\Redis;
use Swoft\Db\Db;
use Swoft\Http\Server\Bean\Annotation\Controller;

/**
 * @Controller(prefix="/httpClient")
 */
class HttpClientController
{
    /**
     * @Inject()
     * @var Redis
     */
    private $redis;
    /**
     * @return array
     * @throws \Swoft\HttpClient\Exception\RuntimeException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function request(): array
    {
        $client = new Client();
        $result = $client->get('http://www.swoft.org')->getResult();
        $result2 = $client->get('http://www.swoft.org')->getResponse()->getBody()->getContents();
        return compact('result', 'result2');
    }
}