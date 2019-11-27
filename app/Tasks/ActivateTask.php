<?php
/**
 * This file is part of Swoft.
 * 任务内容：
 * 供应商7日未登录召回
 * 15天历史发布采购商激活
 * 超过90天没登录的采购商,短信通知
 * 激活效果统计
 */

namespace App\Tasks;

use App\Models\Data\BuyData;
use App\Models\Data\OtherData;
use App\Models\Data\UserData;
use App\Models\Data\UserRecallRecordData;
use App\Models\Logic\GetuiLogic;
use App\Models\Logic\MessageLogic;
use App\Models\Logic\OtherLogic;
use App\Models\Logic\SmsLogic;
use App\Models\Logic\WechatLogic;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * 激活类任务
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
    private $from_days = 4;

    /**
     * 单发消息上限
     * @var int
     */
    private $msg_limit = 500;

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $recommendRedis;

    /**
     * @Inject()
     * @var BuyData
     */
    private $buyData;

    /**
     * @Inject()
     * @var OtherData
     */
    private $otherData;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @Inject()
     * @var UserRecallRecordData
     */
    private $recallData;

    /**
     * @Inject()
     * @var WechatLogic
     */
    private $wechatLogic;

    /**
     * @Inject()
     * @var MessageLogic
     */
    private $messageLogic;

    /**
     * @Inject()
     * @var GetuiLogic
     */
    private $getuiLogic;

    /**
     * @Inject()
     * @var SmsLogic
     */
    private $smsLogic;

    /**
     * 供应商7日未登录召回
     * A cronTab task
     * 3-5 seconds per minute 每天10:00:30执行
     * @Scheduled(cron="30 0 10 * * *")
     * @throws DbException
     */
    public function executionSmsTask()
    {
        Log::info('短信召回任务开启');
        $days = 7;
        $sendCache = 'activate_sms_list:' . date('Y_m_d');

        $expire_time = 7 * 24 * 3600;
        $user_list = $this->OtherLogic->inactive_user_list($this->limit,$days);
        $has_cache = 1;
        if(!empty($user_list)){
            if(!$this->redis->exists($sendCache)){
                $has_cache = 0;
            }
            //短信模板
            $supplier_recall = $this->smsLogic->template_combination('supplier_recall');
            $grayscale = getenv('IS_GRAYSCALE');
            $test_list = $this->userData->getTesters();
            foreach ($user_list as $page) {
                $record = [];
                $phone_list = [];
                $send_record = [];
                foreach ($page as $item) {
                    //测试
                    if(($grayscale == 1 && !in_array($item['userId'], $test_list))){
                        continue;
                    }
                    if(!$this->redis->hExists($sendCache,$item['userId'])) {
                        $visit_name = '有人';
                        $visit_user = $this->userData->getLastVisitInfo($item['userId']);
                        if(!empty($visit_user)){
                            $visit_name = $visit_user['name'];
                        }
                        //改变量模板发送
                        $phone_list[] = [$item['phone'],$visit_name];

                        //预存发送日志
                        $rec = [
                            'user_id' => $item['userId'],
                            'phone' => $item['phone'],
                            'msg_type' => 8,
                            'send_time' => time(),
                            'msg_content' => $supplier_recall,
                            'send_status' => 1
                        ];
                        $record[] = $rec;
                        $send_record[$item['userId']] = $visit_name . '#' . $item['level'] . '#' . $item['safePrice'] . '#' . $item['cid'] . '#' . time();
                    }
                }

                if(!empty($phone_list)){
                    $send_list = [];
                    $send_string = '';
                    //转换变量内容格式为"手机号,变量1,变量2;手机号2,变量1,变量2"
                    foreach ($phone_list as $item) {
                        if(($grayscale == 1 && !in_array($item[0], $this->userData->getSetting('version_811_test_phone')))){
                            continue;
                        }
                        $send_list[] = implode(',',$item);
                    }
                    if(!empty($send_list)){
                        $send_string = implode(';',$send_list);
                    }
                    //批量发送
                    $send_result = $this->smsLogic->send_sms_message('',$supplier_recall,2,$send_string);
                    if($send_result && !empty($record)){
                        $this->OtherLogic->activate_sms_records($record);
                    }
                }

                if(!empty($send_record)){
                    $this->redis->hMset($sendCache,$send_record);
                    if($has_cache == 0){
                        $this->redis->expire($sendCache,$expire_time);
                        $has_cache = 1;
                    }
                }
            }
        }
        Log::info('短信召回任务结束');
        return '短信召回任务';
    }

    /**
     * 供应商7日未登录消息召回
     * 3-5 seconds per minute 每天15:00:00执行
     * @Scheduled(cron="0 0 15 * * *")
     * @throws DbException
     */
    public function executionMsgTask()
    {
        Log::info('通知召回任务开启');
        $sendCache = 'activate_sms_list:' . date('Y_m_d');
        $send_time = time();

        if(!$this->redis->exists($sendCache)){
            Log::info('不存在已发送记录');
        }
        $user_list = $this->redis->hGetAll($sendCache);
        $total = count($user_list);
        $msg_count = 0; //系统消息数
        $getui_count = 0; //个推消息数
        $active_num = 0; //短信已激活数
        if(!empty($user_list)){
            $getui_list = [];
            $grayscale = getenv('IS_GRAYSCALE');
            $test_list = $this->userData->getTesters();
            foreach ($user_list as $key => $item) {
                //测试
                if(($grayscale == 1 && !in_array($key, $test_list))){
                    continue;
                }
                $info = explode('#',$item);
                $last_info_list = $this->userData->getUserLoginVersion($key,['system','addtime']);
                $last_info = current($last_info_list);
                $sms_time = isset($info[4]) ? $info[4] : time();
                if(isset($last_info['addtime']) && $last_info['addtime'] > $sms_time){
                    $active_num += 1;
                    continue;
                }

                //缓存包含3个元素：访客，商户等级，保证金,cid,短信发送时间
                if(count($info) == 5){
                    //消息模板判断 [等级金牌以上|保证金用户]
                    if($info[1] > 2 || $info[2] > 3000){
                        $supplier_recall = $this->messageLogic->template_combination('pay_supplier_no_login_7th',['>X<',$info[0]]);
                    }else{
                        //普通用户
                        $supplier_recall = $this->messageLogic->template_combination('free_supplier_no_login_7th',['>X<',$info[0]]);
                    }

                    //cid空或者最后一次登录设备为安卓,系统消息
                    if($info[3] == '' || (isset($last_info['system']) && $last_info['system'] == 1) || empty($last_info)){
                        $this->messageLogic->send_system_message('1',$key,$supplier_recall);
                        $msg_count+=1;
                    }else{
                        //个推模板生成
                        $cid = (string)$info[3];
                        if(empty($cid)){
                            continue;
                        }
                        $content = (string)$supplier_recall['msgContent'];
                        //消息模板获取
                        $msg_template = $this->getuiLogic->template_combination('supplier_no_login_7th',$cid,'有人浏览了您的店铺',$content,'',['type' => 11]);
                        $getui_list[] = $msg_template;
                        $getui_count += 1;
                    }
                    if($getui_list){
                        try {
                            $this->getuiLogic->send_push_message($getui_list, 1);
                        } catch (\Exception $e) {
                            write_log(5,$e->getMessage());
                        }
                    }
                }
            }
        }
        Log::info($sendCache . '当天总提醒人数：' . $total . ',短信激活数：' . $active_num . ',系统消息提醒数:'. $msg_count . ',个推提醒数：' . $getui_count);
        Log::info('通知召回任务结束');
        return '短信召回任务';
    }

    /**
     * 历史发布采购商激活
     * 每分钟执行
     * @Scheduled(cron="20 * * * * *")
     * @return string
     */
    public function historicalBuyTask()
    {
        Log::info('历史发布采购商微信激活开启');
        $start_time = strtotime(date('Y-m-d H:i',strtotime('-15 day')));
        $end_time = $start_time + 59;
        $params = [
            ['add_time','between',$start_time,$end_time],
            'is_audit' => 0
        ];
        $buy_list = $this->buyData->getLastBuyIds($params);
        if(!empty($buy_list)){
            Log::info("提醒采购id列表:" . json_encode($buy_list));
            $search_params = [
                ['buy_id','IN',$buy_list]
            ];
            $buy_info_list = $this->buyData->getBuyList($search_params,['buy_id','remark','amount','unit','expire_time','user_id','add_time']);
            if(!empty($buy_info_list)){
                $config = \Swoft::getBean('config');
                $msg_temp = $config->get('last_buy_msg');
                $tempId = $msg_temp['temp_id'];
                $send_count = 0;
                $send_user_list = [];
                foreach ($buy_info_list as $item) {
                    //发送数不大于1000人
                    if($send_count >= 1000){
                        break;
                    }
                    //判断是否是最后一条
                    $add_time = $item['addTime'] + 1;
                    $user_buy_list = $this->buyData->getUserByIds($item['userId'],$add_time);
                    if(empty($user_buy_list)){
                        $openId = $this->userData->getUserOpenId($item['userId']);
                        if(!empty($openId)){
                            Log::info("用户{$item['userId']}发送提醒消息");
                            $msg_temp['keyword1']['value'] = $item['buyId'];
                            $msg_temp['keyword2']['value'] = (string)$item['remark'];
                            $msg_temp['keyword3']['value'] = (string)$item['amount'] . $item['unit'];
                            $msg_temp['keyword4']['value'] = empty($item['expireTime']) ? '' : date('Y年n月j日 H:i:s', $item['expireTime']);
                            $this->wechatLogic->send_wechat_message($openId, $tempId, $msg_temp);
                            $send_count += 1;
                            $send_user_list[] = $item['userId'];
                        }
                    }else{
                        Log::info("用户{$item['userId']}有发布最新采购，不发送消息");
                    }
                }
                write_log(2,'15天前发布过采购微信模板消息接收人：' . json_encode($send_user_list));
            }
        }
        Log::info('历史发布采购商微信激活结束');
        return '历史发布采购商微信提醒';
    }

    /**
     * 超过3天未登录的采购商
     * @Scheduled(cron="07 0 10 * * *")
     * @return string
     * @throws DbException
     */
    public function unLogin3thBuyTask()
    {
        Log::info('3天未登录采购商通知开始');
        $user_tag_cache_key = 'user_customer_tag:';
        $start = strtotime(date('Y-m-d',strtotime("-{$this->from_days} day")));
        $end = $start + 24 * 3600;
        $last_id = 0;
        $send_time = time();
        $expire_days = 10;
        $originlink = $this->userData->getSetting('redirect_search_list_originlink_page');
        $min_nums = 50;
        $params = [
            ['last_time','between',$start,$end],
            ['role','in',[1,5]],
            'status' => 1,
            ['user_id','>',$last_id]
        ];
        $user_list_count = $this->userData->getUserCountByParams($params);
        $field = ['user_id','reg_time','cid','role'];
        $pages = ceil($user_list_count/$this->msg_limit);
        $grayscale = getenv('IS_GRAYSCALE');
        $test_list = $this->userData->getTesters();
        if($pages > 0){
            for ($i = 0;$i< $pages; $i++){
                $params[] = ['user_id','>',$last_id];
                $user_info_list = $this->userData->getUserDataByParams($params,$this->msg_limit,$field);
                if(!empty($user_info_list)){
                    $send_result = true;
                    $getui_msg_list = [];
                    $data = [];
                    foreach ($user_info_list as $key => $item) {
                        //测试
                        if(($grayscale == 1 && !in_array($item['userId'], $test_list))){
                            continue;
                        }
                        //获取最后一次登录设备，以此作为发送系统消息途径的判断
                        $last_info_list = $this->userData->getUserLoginVersion($item['userId'],['system','addtime']);
                        $last_info = current($last_info_list);
                        ### 判断用户类型开始 ###
                        $role = 0;
                        //采购数量
                        $buy_num = $this->buyData->getBuyCount($item['userId']);
                        //报价已读
                        $offer_num = $this->buyData->getUserReceiveOffer($item['userId'],['isread']);
                        $offer_count = count($offer_num);
                        $is_read = 0;
                        if(!empty($offer_num)){
                            foreach ($offer_num as $offer_info) {
                                if($offer_info['isread'] == 1){
                                    $is_read = 1;
                                }
                            }
                        }
                        if($buy_num > 0 && $offer_count > 0 && $is_read == 1){
                            $role = 6;
                        }else if($buy_num > 0 && $offer_count > 0 && $is_read == 0){
                            $role = 5;
                        }else if($buy_num > 0 && $offer_count = 0){
                            $role = 4;
                        }else{
                            //搜索次数
                            $search_times = Db::query("select count(*) as count from sb_product_search_log where user_id = {$item['userId']} AND search_time <= {$end}")->getResult();
                            if(!empty($search_times) && $search_times > 0){
                                $role = 3;
                            }else{
                                //浏览次数
                                $visit_num = Db::query("select count(*) as count from sb_product_records where user_id = {$item['userId']} AND r_time <= {$end}")->getResult();
                                if(!empty($visit_num) && $visit_num > 0){
                                    $role = 2;
                                }else{
                                    $role = 1;
                                }
                            }
                        }
                        ### 判断用户类型结束 ###

                        ### 关键词匹配查看 ###
                        $user_label = '新品';
                        $cache_labels = $this->recommendRedis->zRevRange($user_tag_cache_key . $item['userId'],0,0);
                        if(!empty($cache_labels)){
                            $search_label = current($cache_labels);
                        }

                        $nums = 99;
                        if(!empty($search_label)){
                            $param = [];
                            $param['keyword'] = $search_label;
                            $param['pagesize'] = 0;
                            $param['userId'] = $item['userId'];
                            $num = publicSearch(3,$params,$item['userId']);
                            $nums = $num < $min_nums ? $min_nums : $num;
                        }else{
                            $search_label = $user_label;
                        }
                        ### 关键词匹配结束 ###

                        $supplier_recall = $this->messageLogic->template_combination('buyer_no_login_3th',[['>X<','>Y<'],[$nums,$search_label]],['url' => $originlink]);
                        //cid空或者最后一次登录设备为安卓,系统消息
                        $msg_type = 1;
                        if($item['cid'] == '' || (isset($last_info['system']) && $last_info['system'] == 1) || empty($last_info)){
                            $this->messageLogic->send_system_message('1',$item['userId'],$supplier_recall);
                        }else{
                            //个推模板生成
                            $cid = (string)$item['cid'];
                            $content = (string)$supplier_recall['msgContent'];
                            //消息模板获取
                            $msg_template = $this->getuiLogic->template_combination('buyer_no_login_3th',$cid,'您关注的店铺上新啦',$content,'',['type' => 5,'url' => $originlink]);
                            $msg_type = 2;
                            $getui_msg_list[] = $msg_template;
                        }
                        $insert_data = [];
                        $insert_data['user_id'] = $item['userId'];
                        $insert_data['reg_time'] = $item['regTime'];
                        $insert_data['role'] = in_array($item['role'],[2,3,4]) ? 2 : 1;
                        $insert_data['send_role'] = $role;
                        $insert_data['user_notice_label'] = $search_label;
                        $insert_data['match_num'] = $nums;
                        $insert_data['msg_type'] = $msg_type;
                        $insert_data['send_msg_time'] = $send_time;
                        $insert_data['is_done'] = 0;
                        $insert_data['add_time'] = time();
                        $insert_data['expire_time'] = time() + $expire_days * 24 * 3600;
                        array_push($data, $insert_data);
                        $last_id = $item['userId'];
                    }
                    if($getui_msg_list){
                        try {
                            $this->getuiLogic->send_push_message($getui_msg_list, 1);
                        } catch (\Exception $e) {
                            write_log(5,$e->getMessage());
                            $send_result = false;
                        }
                    }

                    //写入发送记录
                    if(!empty($data) && $send_result){
                        $record = $this->recallData->batch_record_save($data);
                        if($record){
                            $data = [];
                        }
                    }
                }
            }
        }
        Log::info('3天未登录采购商通知结束');
        return '3日未登录采购商提醒';
    }

    /**
     * 超过7天未登录的采购商
     * @Scheduled(cron="00 01 10 * * *")
     * @return string
     * @throws DbException
     */
    public function unLogin7thBuyTask()
    {
        Log::info('7天判断采购商回归开始');
        $originlink = $this->userData->getSetting('redirect_search_list_originlink_page');
        $now_time = time();
        $last_id = 0;
        //短信模板
        $supplier_recall_sms = $this->smsLogic->template_combination('buyer_no_login_7th');
        $params = [
            ['expire_time','>',$now_time],
            ['role','in',[1,5]],
            'is_done' => 0,
            ['urr_id','>',$last_id]
        ];
        $count = $this->recallData->get_recall_count($params);
        $pages = ceil($count/$this->msg_limit);
        $grayscale = getenv('IS_GRAYSCALE');
        $test_list = $this->userData->getTesters();
        if($pages > 0){
            for ($i = 0; $i < $pages; $i++)
            {
                $params[] = ['urr_id','>',$last_id];
                Log::info(json_encode($params));
                $recall_list = $this->recallData->get_recall_list($params,['urr_id','user_id','send_role','send_msg_time','send_sms_time','expire_time','msg_is_return','sms_is_return','user_notice_label','match_num']);
                if(!empty($recall_list)){
                    $record = [];
                    foreach ($recall_list as $item) {
                        //测试
                        if(($grayscale == 1 && !in_array($item['userId'], $test_list))){
                            continue;
                        }
                        $data = [];
                        $user_info = $this->userData->getUserInfo($item['userId']);
                        //3天消息已发送，短信未发送且有效期在3天以内
                        if($item['sendMsgTime'] > 0 && $item['sendSmsTime'] == 0 && $now_time - $item['sendMsgTime'] <= 3600 * 24 * 2 + 1800){
                            if($item['send_msg_time'] < $user_info['lastTime']){
                                $data['msg_is_return'] = 1;
                                $data['is_done'] = 1;
                                $data['update_time'] = $now_time;
                            }
                        }else if($item['sendSmsTime'] > 0 && $now_time < $item['expireTime']){
                            if($item['sendSmsTime'] < $user_info['lastTime']){
                                //已发送短信，且当前记录在10天有效期内
                                $data['sms_is_return'] = 1;
                                $data['is_done'] = 1;
                                $data['update_time'] = $now_time;
                            }
                        }else if($item['sendSmsTime'] == 0 && $item['sendMsgTime'] > 0 && $item['msgIsReturn'] == 0 && $item['smsIsReturn'] == 0 && $now_time - $item['sendMsgTime'] > 3600 * 24 * 2 + 1800){
                            //3天消息已发送，未召回，未发送短信，且超出消息有效期，记录在10天内,无行为不再发送消息
                            if($item['sendRole'] > 1){
                                //获取最后一次登录设备，以此作为发送系统消息途径的判断
                                $last_info_list = $this->userData->getUserLoginVersion($item['userId'],['system','addtime']);
                                $last_info = current($last_info_list);
                                $supplier_recall = $this->messageLogic->template_combination('buyer_no_login_3th',[['>X<','>Y<'],[$item['matchNum'],$item['userNoticeLabel']]]);
                                //cid空或者最后一次登录设备为安卓,系统消息
                                if($user_info['cid'] == '' || (isset($last_info['system']) && $last_info['system'] == 1) || empty($last_info)){
                                    $this->messageLogic->send_system_message('1',$item['userId'],$supplier_recall);
                                }else{
                                    //个推模板生成
                                    $cid = (string)$user_info['cid'];
                                    $content = (string)$supplier_recall['msgContent'];
                                    //消息模板获取
                                    $msg_template = $this->getuiLogic->template_combination('buyer_no_login_3th',$cid,'您关注的店铺上新啦',$content,'',['type' => 5,'url' => $originlink]);
                                    $getui_msg_list[] = $msg_template;
                                }
                            }

                            //批量发送短信列表模板发送
                            $phone_list[] = [$user_info['phone'],$item['matchNum'],$item['userNoticeLabel']];

                            //预存发送日志
                            $rec = [
                                'user_id' => $item['userId'],
                                'phone' => $item['phone'],
                                'msg_type' => 9,
                                'send_time' => $now_time,
                                'msg_content' => $supplier_recall_sms,
                                'send_status' => 1
                            ];
                            $record[] = $rec;
                            $data['update_time'] = $now_time;
                        }
                        if(!empty($data)){
                            $this->recallData->update_recall_info($item['urrId'],$data);
                        }
                        $last_id = $item['urrId'];
                    }

                    if(!empty($phone_list)){
                        $send_list = [];
                        $send_string = '';
                        //转换变量内容格式为"手机号,变量1,变量2;手机号2,变量1,变量2"
                        foreach ($phone_list as $sms) {
                            if(($grayscale == 1 && !in_array($sms[0], $this->userData->getSetting('version_811_test_phone')))){
                                continue;
                            }
                            $send_list[] = implode(',',$sms);
                        }
                        if(!empty($send_list)){
                            $send_string = implode(';',$send_list);
                        }
                        //批量发送
                        $send_result = $this->smsLogic->send_sms_message('',$supplier_recall_sms,2,$send_string);
                        if($send_result && !empty($record)){
                            $this->OtherLogic->activate_sms_records($record);
                        }
                    }
                }
            }
        }
        Log::info('7天判断采购商回归结束');
        return '7日采购商回归及消息提醒';
    }

    /**
     * 超过90天没登录的采购商,短信通知
     * @Scheduled(cron="16 * * * * *")
     * @return string
     */
    public function unLoginBuyTask()
    {
        Log::info('90天未登录采购商短信通知开始');
        $start_time = strtotime(date('Y-m-d H:i',strtotime('-90 day')));
        $end_time = $start_time + 59;
        $params = [
            ['last_time','between',$start_time,$end_time],
            'role' => [1,5]
        ];
        $fields = ['user_id','phone'];
        Log::info(json_encode($params));
        $user_list = $this->userData->getUserDataByParams($params,$this->limit,$fields);
        if(!empty($user_list)){
            $inactive_buyer_msg = $this->smsLogic->template_combination('inactive_buyer');;
            $phone_list = [];
            foreach ($user_list as $item) {
                $phone_list[] = $item['phone'];
            }

            if(!empty($phone_list)){
                write_log(2,'90天未登录通知用户列表：' . json_encode($phone_list));
                $receive_list = implode(',',$phone_list);
                $this->smsLogic->send_sms_message($receive_list,$inactive_buyer_msg,2);
            }
        }
        Log::info('90天未登录采购商短信通知结束');
        return '90天未登录的采购商短信通知';
    }

    /**
     * 15天未登录供应商,短信/系统消息通知
     * @Scheduled(cron="0 50 18 * * *")
     * @throws DbException
     */
    public function fifteenthUnLoginTask()
    {
        Log::info('15天未登录供应商通知任务开启');
        $key_mark = 'auto_send_business_'.date("Y_m_d");
        $start_time = strtotime(date('Y-m-d',strtotime('-15 day')));
        $end_time = $start_time + 24 * 3600;
        $now_time = time();
        $expire_time = $now_time + 15 * 24 * 3600;
        $last_id = 0;
        $params = [
            ['last_time','between',$start_time,$end_time],
            ['role','in',[2,3,4]],
            'status' => 1,
            ['user_id','>',$last_id]
        ];
        $user_count = $this->userData->getUserCountByParams($params);
        $pages = ceil($user_count / $this->msg_limit);

        $send_history = [];
        $has_record = 0;
        if($this->redis->exists($key_mark)){
            $send_cache = $this->redis->get($key_mark);
            $send_history = json_decode($send_cache,true);
            $has_record = 1;
        }

        if($pages > 0){
            $has_sms = 1;
            $activate_remark = 'supplier_no_login_15th';
            $sms_content = $this->smsLogic->template_combination($activate_remark);
            if(empty($sms_content)){
                $has_sms = 0;
            }
            //系统消息
            $msg_content = $this->messageLogic->template_combination($activate_remark);
            $grayscale = getenv('IS_GRAYSCALE');
            $test_list = $this->userData->getTesters();
            for ($i = 0;$i< $pages; $i++){
                $sms_log = [];
                $phone_list = [];
                $msg_user = [];
                $user_list = $this->userData->getUserDataByParams($params,$this->msg_limit,['user_id','phone']);
                if(!empty($user_list)){
                    foreach ($user_list as $item) {
                        if(($grayscale == 1 && !in_array($item['userId'], $test_list))){
                            continue;
                        }
                        //已发送，不发
                        if(in_array($item['userId'],$send_history)){
                            continue;
                        }
                        if($has_sms == 1){
                            if(($grayscale == 1 && !in_array($item['phone'], $this->userData->getSetting('version_811_test_phone')))){
                                continue;
                            }
                            $tmp['phone'] = $item['phone'];
                            $tmp['user_id'] = $item['userId'];
                            $tmp['msg_type'] = 20;
                            $tmp['send_time'] = $now_time;
                            $tmp['exprie_time'] = $expire_time;
                            $tmp['msg_content'] = $sms_content;
                            $sms_log[] = $tmp;
                            $phone_list[] = $item['phone'];
                        }
                        if(!empty($msg_content)){
                            $msg_user[] = (string)$item['userId'];
                        }
                        $last_id = $item['userId'];
                        $params[] = ['user_id','>',$last_id];
                    }
                }

                //发送消息/短信
                if(!empty($phone_list)){
                    $phone_string = implode(',',$phone_list);
                    $smsRes = $this->smsLogic->send_sms_message($phone_string,$sms_content,2);
                    if($smsRes){
                        $this->otherData->saveRecords($sms_log);
                    }
                }

                if(!empty($msg_user)){
                    $this->messageLogic->send_system_message('1',$msg_user,$msg_content,1);
                }

                //记录发送历史
                $send_history = array_merge($send_history,$msg_user);
                if($has_record == 0){
                    $this->redis->setex($key_mark,48*3600,json_encode($send_history));
                    $has_record = 1;
                }else{
                    $this->redis->set($key_mark,json_encode($send_history));
                }
            }
        }
        Log::info('15天未登录供应商通知任务结束');
        return '15天未登录供应商通知';
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
        return ['短信召回统计'];
    }
}
