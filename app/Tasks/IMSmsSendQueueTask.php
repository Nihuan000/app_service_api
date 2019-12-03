<?php
/**
 * Created by PhpStorm.
 * Date: 18-9-12
 * Time: 下午2:40
 */
namespace App\Tasks;

use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Task;
use Swoft\Bean\Annotation\Value;
use Swoft\Bean\Annotation\Inject;
use Swoft\Task\Bean\Annotation\Scheduled;

/**
 * IMSmsSendQueue task
 *
 * @Task("IMSmsSendQueue")
 */
class IMSmsSendQueueTask
{
    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;


    private $queue_key = 'msg_queue_list';


    /**
     * 消息发送队列
     * @author Nihuan
     */
    public function sendTask()
    {
        $return_msg = '';
        $now_time = time();
        $queue = $this->redis->has($this->queue_key);
        if($queue){
            $msg = $this->redis->lPop($this->queue_key);
            $secret_code = md5($msg);
            if($this->redis->has($secret_code)){
                $return_msg = '发送记录已存在，跳过';
            }
            $msg_info = json_decode($msg,true);
            if($msg_info['timedTask'] > 0 && $msg_info['timedTask'] < $now_time){
                //如果设置了定时 & 发送时间没有到，重新写入队列，等待轮循
                $msg_json = json_encode($msg_info);
                $this->redis->rPush($this->queue_key,$msg_json);
            }else{
                sendInstantMessaging($msg_info['fromId'], (string)$msg_info['targetId'], json_encode($msg_info['msgExtra']));
                $this->redis->set($secret_code,$msg,180);
                $return_msg = '发送成功';
            }
        }
        return [$return_msg];
    }
}
