<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-21
 * Time: 下午5:43
 */

namespace App\Models\Logic;


use App\Models\Dao\BuyDao;
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
     * @Inject()
     * @var BuyDao
     */
    private $buyDao;

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
     * @param int $user_id
     * @return int
     * @throws \Swoft\Db\Exception\DbException
     */
    public function new_reg_recommend(int $user_id)
    {
        $msg_count = 0;
        $now_date = date('Y-m-d');
        $recommend_key = '@RecommendMsgQueue_' .$now_date;
        $user_tag_list = $this->userDao->getUserTagByUid($user_id);
        $subscription_tag = [];
        if(!empty($user_tag_list)){
            foreach ($user_tag_list as $item) {
                $subscription_tag[] = $item['tag_id'];
            }
            $tag_list = array_splice($subscription_tag,0,4);
            if(!empty($tag_list)){
                foreach ($tag_list as $tag) {
                    $buy_info = $this->buyDao->getBuyInfoByTagId($tag);
                    if(!empty($buy_info)){
                        $match_buy = current($buy_info);
                        if(!empty($match_buy)){
                            $this->redis->lPush($recommend_key,$user_id . '#' . $buy_info['buy_id']);
                            $msg_count += 1;
                        }
                    }
                }
            }
        }
        return $msg_count;
    }
}