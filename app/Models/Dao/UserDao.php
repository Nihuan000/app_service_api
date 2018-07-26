<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Dao;

use Swoft\Bean\Annotation\Bean;
use App\Models\Entity\User;

/**
 * 用户数据对象
 * @Bean()
 * @uses UserDao
 * @author Nihuan
 */
class UserDao
{
    /**
     * 主键获取用户信息
     * @author Nihuan
     * @param int $uid
     * @return mixed
     */
    public function getUserInfoByUid(int $uid)
    {
        return User::findById($uid)->getResult();
    }


    /**
     * 根据id列表获取field
     * @author Nihuan
     * @param array $user_ids
     * @param array $fields
     * @return mixed
     */
    public function getInfoByUids(array $user_ids, array $fields)
    {
        return User::findAll(['user_id' => $user_ids],['field' => $fields])->getResult();
    }
}
