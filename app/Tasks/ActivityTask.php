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
use App\Models\Logic\SmsLogic;
use App\Models\Logic\UserLogic;
use Swoft\Bean\Annotation\Inject;
use App\Models\Logic\OtherLogic;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class ActivityTask - define some tasks
 *
 * @Task("Activity")
 * @package App\Tasks
 */
class ActivityTask{

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
     * @Inject()
     * @var OtherLogic
     */
    private $OtherLogic;

    /**
     * @Inject()
     * @var SmsLogic
     */
    private $smsLogic;

    /**
     * @var string
     */
    private $sys_redis_key = 'sys_msg_of_finish_encourage_task';

    /**
     * @var string
     */
    private $sms_redis_key = 'sms_msg_of_finish_encourage_task';

     /**
     * @var string
     */
    private $sys_nums_redis_key = 'type_two_sys_nums_of_finish_encourage_task';

    /**
     * @var string
     */
    private $sms_nums_redis_key = 'type_two_sms_nums_of_finish_encourage_task';

    /**
     * 激励活动最后一天系统消息
     * 每分钟36秒执行一次
     * @Scheduled(cron="36 * * * * *")
     * @throws DbException
     */
    public function encourageActicitySysTask()
    {
        $end_time = time();
        $start_time = time()-600;
        $user_list = $this->redis->zRangeByScore($this->sys_redis_key, $start_time, $end_time);
        if(!empty($user_list)){
            $time = date('Y-m-d H:i:s', time());
            foreach ($user_list as $value) {
                $user_id = (int)$value;
                Log::info("用户id:{$user_id}在{$time}激励活动系统消息发送开始");
                write_log(3,"用户id:{$user_id}在{$time}激励活动系统消息发送开始");

                //发送系统消息
                $config = \Swoft::getBean('config');
                $sys_msg = $config->get('sysMsg');
                $extra = $sys_msg;
                $extra['title'] =  $extra['msgTitle'] = "";
                $extra['isRich'] = 0;
                $extra['msgContent'] = '您的408元的实力商家礼包将于今日过期，快去激活吧！';
                $extra['content'] = '您的408元的实力商家礼包将于今日过期，#快去激活吧#！';
                $d = [["keyword"=>"#快去激活吧#","type"=>18,"id"=>0,"url"=>$this->userData->getSetting('nine_encourage_activity_page')]];
                $datashow = array();
                $extra['data'] = $d;
                $extra['commendUser'] = array();
                $extra['showData'] = $datashow;
                $data['extra'] = $extra;

                sendInstantMessaging('1',(string)$user_id,json_encode($data['extra']));
                Log::info("用户id:{$user_id}的激励活动系统消息发送完成");
                write_log(3,"用户id:{$user_id}的激励活动系统消息发送完成");

                $this->redis->zRem($this->sys_redis_key, $value);
            }

            // 计数
            $type_two_sys_nums = $this->redis->get($this->sys_nums_redis_key);
            $type_two_sys_nums = empty($type_two_sys_nums) ? 0 : $type_two_sys_nums;
            $type_two_sys_nums += count($user_list);
            $this->redis->set($this->sys_nums_redis_key, $type_two_sys_nums);
        }
    }

    /**
     * 激励活动最后一天短信消息
     * 每分钟36秒执行一次
     * @Scheduled(cron="36 * * * * *")
     * @throws DbException
     */
    public function encourageActicitySmsTask()
    {
        $end_time = time();
        $start_time = time()-600;
        $user_list = $this->redis->zRangeByScore($this->sms_redis_key, $start_time, $end_time);
        if(!empty($user_list)){
            $time = date('Y-m-d H:i:s', time());
            $record = [];
            foreach ($user_list as $value) {
                $user_id = (int)$value;
                Log::info("用户id:{$user_id}在{$time}激励活动短信消息发送开始");
                write_log(3,"用户id:{$user_id}在{$time}激励活动短信消息发送开始");

                //发送短信消息
                $sms_short_url = $this->userData->getSetting('nine_encourage_activity_sms_url');

                $msg = "【搜布】您的408元的实力商家礼包将于今日过期，快去激活吧！ $sms_short_url";

                if(env('SMS_SWITCH') == 1 && !empty($msg)){
                    write_log(3,"鼓励消息允许发送");
                    $user_info = $this->userData->getUserInfo($user_id);
                    write_log(3,"user_info:".json_encode($user_info));
                    if(!empty($user_info)){
                        $result = $this->smsLogic->send_sms_message($user_info['phone'],$msg,2);
                        write_log(3,"短信发送结果:".json_encode($result));
                        if($result){
                            $rec = [
                                'user_id' => $user_id,
                                'phone' => $user_info['phone'],
                                'msg_type' => 19,
                                'send_time' => time(),
                                'msg_content' => $msg,
                                'send_status' => 1
                            ];
                            $record[] = $rec;
                        }
                    }
                }

                Log::info("用户id:{$user_id}的激励活动短信消息发送完成");
                write_log(3,"用户id:{$user_id}的激励活动短信消息发送完成");

                $this->redis->zRem($this->sms_redis_key, $value);
            }
            // 入库
            $this->OtherLogic->activate_sms_records($record);

            // 计数
            $type_two_sms_nums = $this->redis->get($this->sms_nums_redis_key);
            $type_two_sms_nums = empty($type_two_sms_nums) ? 0 : $type_two_sms_nums;
            $type_two_sms_nums += count($user_list);
            $this->redis->set($this->sms_nums_redis_key, $type_two_sms_nums);
        }
    }
}
