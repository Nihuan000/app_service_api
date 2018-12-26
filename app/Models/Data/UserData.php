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
use Swoft\Redis\Redis;

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
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @author Nihuan
     * @param $uid
     * @return mixed
     */
    public function getUserInfo($uid)
    {
        return $this->userDao->getUserInfoByUid($uid);
    }


    /**
     * 根据id列表获取用户信息
     * @author Nihuan
     * @param $user_ids
     * @param array $fields
     * @return mixed
     */
    public function getUserByUids($user_ids, $fields = ['*'])
    {
        return $this->userDao->getInfoByUids($user_ids, $fields);
    }

    /**
     * author: nihuan
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getTesters()
    {
        $is_delete = 0;
        $type = 5;
        return $this->userDao->getTestersInfo($is_delete, $type);
    }

    /**
     * @param $user_id
     * @param $tag_id
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function isUserTagInActivity($user_id, $tag_id)
    {
        $tag_meet_at = 0;
        $cache_index = 'purchase_goods_index:';
        if($this->redis->sIsmember($cache_index . $tag_id,$user_id)){
            return $tag_meet_at = 1;
        }
        $user_tag_string = $this->redis->get('user_subscription_tag:' . $user_id);
        $user_tag_list = json_decode($user_tag_string,true);
        if(empty($user_tag_list)){
            $user_tag_list = $this->userDao->getUserTagByUid($user_id);
            if(!empty($user_tag_list)){
                $this->redis->set('user_subscription_tag:' . $user_id,json_encode($user_tag_list));
            }
        }
        if(!empty($user_tag_list)){
            foreach ($user_tag_list as $item) {
                if($tag_id == $item['top_id']){
                    $this->redis->sAdd($cache_index . $item['top_id'],$user_id);
                    $tag_meet_at = 1;
                }
            }
        }
        return $tag_meet_at;
    }

    /**
     * 实商列表
     * @param array $params
     * @param array $field
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getStrengthList(array $params = [], array $field = [])
    {
        return $this->userDao->getUserStrengthList($params, $field);
    }

    /**
     * @param array $user_id
     * @param string $tag
     * @return mixed
     */
    public function getUserByTag(array $user_id, string $tag)
    {
        return $this->userDao->getUserListBySecTag($user_id,$tag);
    }
}
