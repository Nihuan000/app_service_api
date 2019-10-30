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
     * @var string
     */
    private $safe_price_finish_user_list_queue = 'safe_price_finish_user_list_queue';

     /**
     * @var string
     */
    private $safe_price_finish_user_sms_queue = 'safe_price_finish_user_sms_queue';

    /**
     * @var string
     */
    private $new_register_buyer_sys_msg = 'new_register_buyer_sys_msg';

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
        $safe_price_list = $this->redis->zRangeByScore($this->safe_price_msg_queue, $start_time, $end_time);
        if(!empty($safe_price_list)){
            $user_id = 0;
            foreach ($safe_price_list as $key => $value) {
                if($key == 0){
                    $user_id = $value;
                    $this->redis->zDelete($this->safe_price_msg_queue, $value);
                    $delay_time = $this->userData->getSetting('msg_delay_time');
                    $delay_time = empty($delay_time) ? 180 : $delay_time;
                    $use_time = $delay_time + time();
                    $this->redis->zAdd($this->safe_price_finish_user_list_queue, $use_time, $value);
                    $this->redis->zAdd($this->safe_price_finish_user_sms_queue, $use_time, $value);
                }else{
                    $this->redis->zAdd($this->safe_price_msg_queue, time(),$value);
                }
            }
            $user_id = (int)$user_id;
            $time = date('Y-m-d H:i:s', time());
            write_log(3,"保证金提取的用户".$user_id);
            Log::info("用户id:{$user_id}在{$time}开始提取保证金");
            write_log(3,"用户id:{$user_id}在{$time}开始提取保证金");
            $res = $this->UserLogic->pick_up_safe_price($user_id);
            if($res['status'] == 1){
                $time = date('Y-m-d H:i:s', time());
                Log::info("用户id:{$user_id}在{$time}保证金Log完成");
                write_log(3,"用户id:{$user_id}在{$time}保证金Log完成");
            }else{
                $reason = $res['reason'];
                Log::info("用户id:{$user_id}提取保证金失败,原因:{$reason}");
                write_log(3,"用户id:{$user_id}提取保证金失败,原因:{$reason}");
                //　再次将用户放入对列
                $next_time = time();
                $this->redis->zAdd($this->safe_price_msg_queue, $next_time, $user_id);
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
                Log::info("用户id:{$user_id}首次缴纳保证金消息发送完成");
                write_log(3,"用户id:{$user_id}首次缴纳保证金消息发送完成");
            }
        }
    }

    /**
     * 用户保证金提取完成之后的消息发送操作
     * 每分钟36秒执行一次
     * @Scheduled(cron="36 * * * * *")
     * @throws DbException
     */
    public function safePriceFinishMsgTask()
    {
        $end_time = time();
        $start_time = time()-600;
        $safe_price_msg_list = $this->redis->zRangeByScore($this->safe_price_finish_user_list_queue, $start_time, $end_time);
        if(!empty($safe_price_msg_list)){
            write_log(3,"保证金提取完成,消息发送的用户数组".json_encode($safe_price_msg_list));
            foreach ($safe_price_msg_list as $key => $value) {
                $this->redis->zDelete($this->safe_price_finish_user_list_queue, $value);
            }
            $time = date('Y-m-d H:i:s', time());
            foreach ($safe_price_msg_list as $key => $value) {
                $user_id = (int)$value;
                Log::info("用户id:{$user_id}在{$time}开始发送保证金完成消息");
                write_log(3,"用户id:{$user_id}在{$time}开始发送保证金完成消息");
                $msg = "尊敬的供应商您好，恭喜您保证金成功提现到余额，进入我的钱包即可查看。";
                $res = []; 
                $res['extra']['title'] = '温馨提示';
                $res['extra']['content'] = $msg;
                $res['extra']['msgContent'] = $msg;
                sendInstantMessaging('1',(string)$user_id,json_encode($res['extra']));
                Log::info("用户id:{$user_id}提取保证金消息推送完成");
                write_log(3,"用户id:{$user_id}提取保证金消息推送完成");
            }
        }
    }


    /**
     * 用户保证金提取完成之后的消息发送操作
     * 每分钟36秒执行一次
     * @Scheduled(cron="36 * * * * *")
     * @throws DbException
     */
    public function newRegisterBuyerTask()
    {
        $end_time = time();
        $start_time = time()-600;
        $reg_buyer_list = $this->redis->zRangeByScore($this->new_register_buyer_sys_msg, $start_time, $end_time);
        if(!empty($reg_buyer_list)){
            write_log(3,"新注册的采购商".json_encode($reg_buyer_list));
            foreach ($reg_buyer_list as $key => $value) {
                $this->redis->zDelete($this->new_register_buyer_sys_msg, $value);
            }
            $time = date('Y-m-d H:i:s', time());
            $is_test = $this->userData->getSetting('is_test');
            $is_test = empty($is_test) ? 0 : 1;
            $start_time = strtotime(date('Y-m-d', $time - 24 *3600));
            $end_time = strtotime(date('Y-m-d 23:59:59', $time - 24 *3600));
            $satisfy_demand_user_list = [];
            foreach ($reg_buyer_list as $key => $value) {
                $is_login = $this->userData->checkUserLogin($value, $start_time, $end_time);
                if(empty($is_login)){
                    $$satisfy_demand_user_list[] = (string)$value;
                }
            }
            write_log(3,"在{$time}时间开始给用户id:".json_encode($satisfy_demand_user_list)."发送系统消息");
            $msg = "你好，请问需要找什么面料？";
            $send_user_id = $is_test == 1 ? '173173' : '236359';
            sendC2CMessaging($send_user_id,$satisfy_demand_user_list,$msg, 1, 1);

            write_log(3,"在{$time}时间给用户id:{$user_id}发送系统消息完成");
        }
    }
}
