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
use Swoft\Db\Db;

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

    /**
     * @param int $user_id
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserTagByUid(int $user_id)
    {
        $list = Db::query("select user_id, tag_id,tag_name, parent_id, parent_name, top_name, top_id from sb_user_subscription_tag where user_id= {$user_id}")->getResult();
        return $list;
    }


    /**
     * author: nihuan
     * @param int $is_delete
     * @param int $type
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getTestersInfo(int $is_delete, int $type)
    {
        $user_list = [];
        $list = Db::query("select uid from sb_agent_user WHERE is_delete = {$is_delete} AND type = {$type}")->getResult();
        if(!empty($list)){
            foreach ($list as $user) {
                $user_list[] = $user['uid'];
            }
        }

        return $user_list;
    }
}
