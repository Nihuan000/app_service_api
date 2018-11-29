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

    /**
     * 屏蔽标签处理
     * @param array $event
     */
    public function set_tag_level(array $event)
    {
        $cacheKey = '';
        switch ($event['set_type']){
            case 1: //不匹配
                $cacheKey = '@Mismatch_tag_' . $event['user_id'];
                break;

            case 2: //屏蔽
                $cacheKey = '@Shield_tag_' . $event['user_id'];
                break;
        }

        if(!empty($cacheKey)){
            $keys = $this->redis->hGet($cacheKey,$event['tag_type']);
            if(!empty($keys)){
                $list = json_decode($keys,true);
                $ids = array_merge($list,$event['tag_ids']);
            }else{
                $ids = $event['tag_ids'];
            }

            $tag_ids = json_encode($ids);
            $this->redis->hSet($cacheKey,$event['tag_type'],$tag_ids);
        }
    }
}