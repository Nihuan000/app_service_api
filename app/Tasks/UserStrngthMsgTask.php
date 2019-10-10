<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Tasks;

use App\Models\Data\UserData;
use App\Models\Logic\UserLogic;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class UserStrngthMsgTask - define some tasks
 *
 * @Task("UserStrngthMsg")
 * @package App\Tasks
 */
class UserStrngthMsgTask{

    /**
     * @Inject("appRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @var string
     */
    private $redis_key = 'inter_strength_page_userlist';


    /**
     * 用户保证金提取操作
     * 每分钟26秒执行一次
     * @Scheduled(cron="26 * * * * *")
     * @throws DbException
     */
    public function safePriceTask()
    {
        $end_time = time();
        $start_time = time()-600;
        $user_list = $this->redis->zRangeByScore($this->redis_key, $start_time, $end_time);
        if(!empty($user_list)){
            $time = date('Y-m-d H:i:s', time());
            foreach ($user_list as $key => $value) {
                $user_id = (int)$value;
                Log::info("用户id:{$user_id}在{$time}9.9体验实力商家消息发送开始");
                write_log(3,"用户id:{$user_id}在{$time}9.9体验实力商家消息发送开始");
               
                //发送系统消息
                $config = \Swoft::getBean('config');
                $sys_msg = $config->get('sysMsg');
                $extra = $sys_msg;
                $extra['title'] =  $extra['msgTitle'] = "";
                $extra['isRich'] = 0;
                $extra['msgContent'] = '恭喜您获得9.9元购买199元体验实力商家资格，点击查看活动。';
                $extra['content'] = '恭喜您获得9.9元购买199元体验实力商家资格，#点击查看活动#。';
                $d = [["keyword"=>"#点击查看活动#","type"=>18,"id"=>0,"url"=>$this->userData->getSetting('nine_activity_system_url')]];
                $datashow = array();
                $extra['data'] = $d;
                $extra['commendUser'] = array();
                $extra['showData'] = $datashow;
                $data['extra'] = $extra;

                sendInstantMessaging('1',(string)$user_id,json_encode($data['extra']));
                Log::info("用户id:{$user_id}的9.9体验实力商家消息发送完成");
                write_log(3,"用户id:{$user_id}的9.9体验实力商家消息发送完成");

                $this->redis->zDelete($this->redis_key, $value);
            }
        }
    }
}
