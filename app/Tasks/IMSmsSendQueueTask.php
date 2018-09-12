<?php
/**
 * Created by PhpStorm.
 * Date: 18-9-12
 * Time: 下午2:40
 */

/**
 * IMSmsSendQueue task
 *
 * @Task("IMSmsSendQueue")
 */
class IMSmsSendQueueTask
{
    /**
     * @Inject("DemoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Value(env="${MESSAGE_QUEUE_LIST}")
     * @var string
     */
    private $queue_key;


    /**
     * 消息发送队列
     * @author Nihuan
     * @Scheduled(cron="0 * 7-22 * * *")
     */
    public function sendTask()
    {
        $now_time = time();
        $queue = $this->redis->exists($this->queue_key);
        if($queue){
            $msg = $this->redis->lPop($this->queue_key);
            $msg_info = json_decode($msg,true);
            if($msg_info['timedTask'] > 0 && $msg_info['timedTask'] < $now_time){
                //如果设置了定时 & 发送时间没有到，重新写入队列，等待轮循
                $msg_json = json_encode($msg_info);
                $this->redis->rPush($this->queue_key,$msg_json);
            }else{
                sendInstantMessaging($msg_info['fromId'], (string)$msg_info['targetId'], json_encode($msg_info['msgExtra']));
            }
        }
    }
}