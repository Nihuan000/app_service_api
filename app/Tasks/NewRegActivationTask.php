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
use Swoft\Bean\Annotation\Inject;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class NewRegActivationTask - define some tasks
 *
 * @Task("NewRegActivation")
 * @package App\Tasks
 */
class NewRegActivationTask{

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;
    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * 待推送队列
     * @var string
     */
    private $wait_push_list = 'wait_activation_push_list_';

    /**
     * 供应商激活聊天消息
     * @var string
     */
    private $supplier_msg = '你好，请问店铺能上传点别的布料看看吗？';

    /**
     * 供应商注册激活消息
     * per minute 每分钟执行
     *
     * @Scheduled(cron="40 * * * * *")
     */
    public function SupplierTask()
    {
        $hour = date('H');
        $start_time = strtotime('-30 minute');
        $end_time = $start_time + 59;
        Log::info('供应商注册激活任务开始');
        $params = [
            ['reg_time', 'between', $start_time, $end_time],
            ['role','IN',[2,3,4]],
            'status' => 1
        ];
        $new_reg = $this->userData->getUserDataByParams($params,500);
        if(!empty($new_reg)){
            $user_list = array_column($new_reg,'userId');
            if(!empty($user_list)){
                $reg_date = date('Y-m-d H:i:s',$start_time);
                write_log(2,"供应商激活，注册时间：{$reg_date}, 用户列表:" . json_encode($user_list));
                if($hour > 21 || $hour < 8){
                    //写入待推送缓存
                    $next_day = $hour > 21 ? date('Y_m_d',strtotime('+1 day')) : date('Y_m_d');
                    $this->redis->rPush($this->wait_push_list . $next_day, 'new_supplier#' . json_encode($user_list));
                    write_log(2,'不在可发送时间内，写入待推送队列');
                }else{
                    //发送推送
                    $msg = $this->supplier_msg;
                    sendC2CMessaging('236359',$user_list,$msg,1);
                }
            }
        }
        Log::info('供应商注册激活任务结束');
        return '供应商注册激活';
    }

    /**
     * 采购商注册次日激活消息
     * 每分钟执行
     * @Scheduled(cron="50 * * * * *")
     */
    public function buyersTask()
    {
        $hour = date('H');
        $start_time = strtotime('-1 day');
        $end_time = $start_time + 59;
        Log::info('采购商注册激活任务开始');
        $params = [
            ['reg_time', 'between', $start_time, $end_time],
            ['role','IN',[1,5]],
            'status' => 1
        ];
        $new_reg = $this->userData->getUserDataByParams($params,500);
        if(!empty($new_reg)){
            $user_list = array_column($new_reg,'userId');
            if(!empty($user_list)){
                $reg_date = date('Y-m-d H:i',$start_time);
                write_log(2,"采购商次日激活，注册时间：{$reg_date}, 用户列表:" . json_encode($user_list));
                if($hour > 21 || $hour < 8){
                    //写入待推送缓存
                    $next_day = $hour > 21 ? date('Y_m_d',strtotime('+1 day')) : date('Y_m_d');
                    $this->redis->rPush($this->wait_push_list . $next_day, 'new_buyer#' . json_encode($user_list));
                    write_log(2,'不在可发送时间内，写入待推送队列');
                }else{
                    //发送推送
                    $msg = $this->buyer_msg();
                    sendInstantMessaging('1',$user_list,$msg,1);
                }
            }
        }
        Log::info('采购商注册激活任务结束');
        return '采购商注册激活';
    }

    /**
     * 缓存队列消息发送
     * 每天上午10点执行
     *
     * @Scheduled(cron="0 0 10 * * *")
     */
    public function cacheQueueTask()
    {
        $date = date('Y_m_d');
        Log::info('激活缓存队列任务开始');
        if($this->redis->exists($this->wait_push_list . $date)){
            $push_list = $this->redis->lRange($this->wait_push_list . $date,0,-1);
            if(!empty($push_list)){
                foreach ($push_list as $push) {
                    $msg_info = explode('#',$push);
                    if(is_array($msg_info)){
                        switch ($msg_info[0]){
                            case 'new_supplier':
                                $user_list = json_decode($msg_info[1],true);
                                $msg = $this->supplier_msg;
                                sendC2CMessaging('236359',$user_list,$msg,1);
                                break;

                            case 'new_buyer':
                                $user_list = json_decode($msg_info[1],true);
                                $msg = $this->buyer_msg();
                                sendInstantMessaging('1',$user_list,$msg,1);
                                break;
                        }
                    }
                    $this->redis->zRem($this->wait_push_list . $date,$push);
                }
            }
        }
        Log::info('激活缓存队列任务结束');
        return '缓存激活消息已发送';
    }

    /**
     * 采购商消息
     * @return false|string
     */
    private function buyer_msg()
    {
        $url = $this->userData->getSetting('hot_search_url');
        $config = \Swoft::getBean('config');
        $extra =  $config->get('sysMsg');
        $extra['isRich'] = 0;
        $extra['title'] =  $extra['msgTitle'] = "想知道近期大家都在找什么面料吗？";
        $extra['msgContent'] = "想知道近期大家都在找什么面料吗？ 点击查看";
        $extra['content'] = "你好，请问店铺能上传点别的布料看看吗？#点击查看#";
        $d = [["keyword"=>"#点击查看#","type"=>18,"id"=>0,"url"=> $url]];
        $data_show = array();
        $extra['data'] = $d;
        $extra['commendUser'] = array();
        $extra['showData'] = $data_show;
        $data['extra'] = $extra;

        return json_encode($data['extra']);
    }
}
