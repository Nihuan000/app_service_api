<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-17
 * Time: 下午1:56
 * Desc: 商机推荐提醒消息任务
 */

namespace App\Tasks;

use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * RecommendMsgQueue task
 *
 * @Task("RecommendMsgQueue")
 */
class RecommendMsgQueueTask
{

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * 商机推荐消息提醒发送
     * @author Nihuan
     * @Scheduled(cron="1 * * * * *")
     */
    public function RecommendQueueTask()
    {

    }
}