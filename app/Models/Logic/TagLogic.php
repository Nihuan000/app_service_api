<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-21
 * Time: 下午5:43
 */

namespace App\Models\Logic;


use App\Models\Dao\UserDao;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Bean;
use Swoft\Redis\Redis;

/**
 * 标签逻辑层
 * @Bean()
 * @uses  TagLogic
 * @author Nihuan
 * @package App\Models\Logic
 */
class TagLogic
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
     * @param array $event
     * @throws \Swoft\Db\Exception\DbException
     */
    public function event_analysis(array $event)
    {
        $user_tag_list = $this->userDao->getUserTagByUid($event['user_id']);
        if(!empty($user_tag_list)){
            $this->redis->set('user_subscription_tag:' . $event['user_id'],json_encode($user_tag_list));
        }
    }
}