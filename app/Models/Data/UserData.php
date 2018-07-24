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
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 * 用户数据类
 * @Bean()
 * @uses      UserData
 * @author    Nihuan
 */
class UserData
{
    /**
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * @author Nihuan
     * @param $uid
     * @return mixed
     */
    public function getUserInfo($uid)
    {
        return $this->userDao->getUserInfoByUid($uid);
    }
}
