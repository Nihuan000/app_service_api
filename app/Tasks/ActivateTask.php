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

use App\Models\Logic\OtherLogic;
use Swoft\Bean\Annotation\Inject;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class ActivateTask - define some tasks
 *
 * @Task("Activate")
 * @package App\Tasks
 */
class ActivateTask{

    /**
     * @Inject()
     * @var OtherLogic
     */
    private $OtherLogic;
    private $limit = 1000;

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;
    /**
     * A cronTab task
     * 3-5 seconds per minute 每天10:00:30执行
     *
     * @Scheduled(cron="30 0 10 * * *")
     */
    public function executionTask()
    {
        Log::info('短信召回任务开启');
        $days = 7;
        $sendCache = 'activate_sms_list:' . date('Y_m_d');
        $config = \Swoft::getBean('config');
        $supplier_recall_msg = $config->get('activateSms.supplier_recall');
        if(env('SMS_SWITCH') == 1){
            $short_url = get_shot_url($days,11);
            $supplier_recall = $supplier_recall_msg . $short_url;
        }else{
            $supplier_recall = $supplier_recall_msg;
        }
        $supplier_recall .= ' 退订回T';
        $expire_time = 7 * 24 * 3600;
        $user_list = $this->OtherLogic->inactive_user_list($this->limit,$days);
        $has_cache = 1;
        if(!empty($user_list)){
            if(!$this->redis->exists($sendCache)){
                $has_cache = 0;
            }
            foreach ($user_list as $page) {
                $record = [];
                $phone_list = [];
                $user_list = [];
                foreach ($page as $item) {
                    if(!$this->redis->sIsmember($sendCache,$item['userId'])) {
                        $phone_list[] = $item['phone'];
                        $user_list[] = (string)$item['userId'];
                        $rec = [
                            'user_id' => $item['userId'],
                            'phone' => $item['phone'],
                            'msg_type' => 8,
                            'send_time' => time(),
                            'msg_content' => $supplier_recall,
                            'send_status' => 1
                        ];
                        $record[] = $rec;
                        $this->redis->sAdd($sendCache,$item['userId']);
                        if($has_cache == 0){
                            $this->redis->expire($sendCache,$expire_time);
                            $has_cache = 1;
                        }
                    }
                }
                if(!empty($phone_list) && !empty($user_list)){
                    $phone_string = implode(',',$phone_list);
                    //短信
                    $send_result = sendSms($phone_string,$supplier_recall,2,2,1);
                    //系统消息
                    $config = \Swoft::getBean('config');
                    $sys_msg = $config->get('sysMsg');
                    //发送系统消息
                    ################## 消息基本信息开始 #######################
                    $extra = $sys_msg;
                    $extra['title'] = '有人浏览了您的搜布店铺';
                    $extra['msgContent'] = "刚刚有人浏览了您的搜布店铺，赶快更新下产品吧！ 更新产品";
                    ################## 消息基本信息结束 #######################

                    ################## 消息扩展字段开始 #######################
                    $extraData['keyword'] = '#更新产品#';
                    $extraData['type'] = 12;
                    ################## 消息扩展字段结束 #######################

                    $extra['data'] = [$extraData];
                    $extra['content'] = "刚刚有人浏览了您的搜布店铺，赶快更新下产品吧！ #更新产品#";
                    $notice['extra'] = $extra;
                    sendInstantMessaging('1', $user_list, json_encode($notice['extra']));
                    if($send_result && !empty($record)){
                        $this->OtherLogic->activate_sms_records($record);
                    }
                }
            }
        }
        Log::info('短信召回任务结束');
    }

    /**
     * 激活效果统计
     * 3-5 seconds per minute 每天09:00:30执行
     *
     * @Scheduled(cron="30 0 09 * * *")
     */
    public function statisticsTask()
    {
        Log::info('短信召回统计开启');
        $statistics_key = 'activation_statistic';
        $activate_data = $this->OtherLogic->activation_user_ids();
        if(!empty($activate_data) && $activate_data['send_total'] > 0){
            $cache = [
                'statistic_date' => date('Y-m-d'),
                'send_total' => $activate_data['send_total'],
                'login_count' => $activate_data['login_count'],
                'activate_ratio' => sprintf('%.2f',$activate_data['login_count'] / $activate_data['send_total'])
            ];
            $cache_value = json_encode($cache);
            $this->redis->sAdd($statistics_key,$cache_value);
        }
        Log::info('短信召回统计结束');
    }
}
