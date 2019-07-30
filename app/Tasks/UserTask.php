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
 * Class UserTask - define some tasks
 *
 * @Task("User")
 * @package App\Tasks
 */
class UserTask{

    /**
     * @Inject("appRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject()
     * @var UserLogic
     */
    private $UserLogic;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @var string
     */
    private $safe_price_msg_queue = 'safe_price_msg_queue';

    /**
     * @var string
     */
    private $safe_price_send_msg_msg_queue = 'safe_price_send_msg_msg_queue';

    /**
     * 用户保证金提取操作
     * 每分钟26秒执行一次
     * @Scheduled(cron="26 * * * * *")
     * @throws DbException
     */
    public function safePriceTask()
    {
        $end_time = time();
        $start_time = time()-60;
        $safe_price_list = $this->redis->zRangeByScore($this->safe_price_msg_queue, $start_time, $end_time);
        if(!empty($safe_price_list)){
            write_log(3,"保证金提取的用户数组".json_encode($safe_price_list));
            foreach ($safe_price_list as $key => $value) {
                $this->redis->zDelete($this->safe_price_msg_queue, $value);
            }
            $time = date('Y-m-d H:i:s', time());
            foreach ($safe_price_list as $key => $value) {
                $user_id = (int)$value;
                Log::info("用户id:{$user_id}在{$time}开始提取保证金");
                write_log(3,"用户id:{$user_id}在{$time}开始提取保证金");
                $res = $this->UserLogic->pick_up_safe_price($user_id);
                if($res['status'] == 1){
                    $time = time();
                    Log::info("用户id:{$user_id}在{$time}保证金Log完成");
                    write_log(3,"用户id:{$user_id}在{$time}保证金Log完成");
                    $msg = "尊敬的供应商您好，恭喜您保证金成功提现到余额，进入我的钱包即可查看。";
                    $res = []; 
                    $res['extra']['title'] = '温馨提示';
                    $res['extra']['content'] = $msg;
                    $res['extra']['msgContent'] = $msg;
                    sendInstantMessaging('1',(string)$user_id,json_encode($res['extra']));
                    if($this->userData->getSetting('SEND_SMS') == 1 && !empty($msg)){
                        $user_info = $this->userData->getUserInfo($user_id);
                        if(!empty($user_info)){
                            $msg .= " 退订回T";
                            //sendSms($user_info['phone'],$msg,2,2);
                        }
                    }
                    Log::info("用户id:{$user_id}完成提取保证金");
                    write_log(3,"用户id:{$user_id}完成提取保证金");
                }else{
                    $reason = $res['reason'];
                    Log::info("用户id:{$user_id}提取保证金失败,原因:{$reason}");
                    write_log(3,"用户id:{$user_id}提取保证金失败,原因:{$reason}");
                    //　再次将用户放入对列
                    $next_time = $time;
                    $this->redis->zAdd($this->safe_price_msg_queue, $next_time, $user_id);
                }
            }
        }
    }

    /**
     * 用户保证金提取操作
     * 每分钟56秒执行一次
     * @Scheduled(cron="56 * * * * *")
     * @throws DbException
     */
    public function safePriceSendMsgTask()
    {
        $end_time = time();
        $start_time = time()-60;
        $safe_price_msg_list = $this->redis->zRangeByScore($this->safe_price_send_msg_msg_queue, $start_time, $end_time);
        if(!empty($safe_price_msg_list)){
            write_log(3,"保证金消息发送的用户数组".json_encode($safe_price_msg_list));
            foreach ($safe_price_msg_list as $key => $value) {
                $this->redis->zDelete($this->safe_price_send_msg_msg_queue, $value);
            }
            $time = date('Y-m-d H:i:s', time());
            foreach ($safe_price_msg_list as $key => $value) {
                $user_id = (int)$value;
                Log::info("用户id:{$user_id}在{$time}保证金消息发送开始");
                write_log(3,"用户id:{$user_id}在{$time}保证金消息发送开始");
                $msg = "根据搜布官方统计，每上传一个产品，将增加12%的访客咨询，查看店铺-发布产品，即可轻松上传。";
                $res = []; 
                $res['extra']['title'] = '温馨提示';
                $res['extra']['content'] = $msg;
                $res['extra']['msgContent'] = $msg;
                sendInstantMessaging('1',(string)$user_id,json_encode($res['extra']));
                Log::info("用户id:{$user_id}保证金消息发送完成");
                write_log(3,"用户id:{$user_id}保证金消息发送完成");
            }
        }
    }
}
