<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Logic;

use App\Models\Data\UserData;
use Swoft\Bean\Annotation\Bean;
use Swoft\Rpc\Client\Bean\Annotation\Reference;
use Swoft\Bean\Annotation\Inject;

/**
 * 用户逻辑层
 * 同时可以被controller server task使用
 *
 * @Bean()
 * @uses      UserLogic
 * @version   2017年10月15日
 * @author    stelin <phpcrazy@126.com>
 * @copyright Copyright 2010-2016 swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class UserLogic
{
    /**
     * @Reference("user")
     *
     * @var \App\Lib\DemoInterface
     */
    private $demoService;

    /**
     * @Reference(name="user", version="1.0.1")
     *
     * @var \App\Lib\DemoInterface
     */
    private $demoServiceV2;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;


    public function rpcCall()
    {
        return ['bean', $this->demoService->getUser('12'), $this->demoServiceV2->getUser('16')];
    }

    public function getUserInfo(array $uids)
    {
        $user = [
            'name' => 'boby',
            'desc' => 'this is boby'
        ];

        $data = [];
        foreach ($uids as $uid) {
            $user['uid'] = $uid;
            $data[] = $user;
        }

        return $data;
    }

    /**
     * @param array $params
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function checkUserTagExists(array $params)
    {
        $is_meet = $this->userData->isUserTagInActivity($params['user_id'],$params['tag_id']);
        return $is_meet;
    }
}