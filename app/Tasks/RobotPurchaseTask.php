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
use App\Models\Logic\UserStrengthLogic;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
use Swoft\Db\Query;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class RobotPurchaseTask- define some tasks
 *
 * @Task("robotPurchaseTask")
 * @package App\Tasks
 */
class RobotPurchaseTask{

    /**
     * @Inject()
     * @var UserStrengthLogic
     */
    private $userStrengthLogic;

    /**
     * @Inject("appRedis")
     * @var Redis
     */
    private $appRedis;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * 拼团成功实商补充任务/机器人参团状态修改
     * 7.2-7.4 每分钟第5秒执行
     * @Scheduled(cron="5 * * 2-4 7 *")
     */
    public function groupStrengthTask()
    {
        $now_time = time() - 30;
        Log::info('拼团实商同步任务开启');
        $order_list = Query::table('sb_group_purchase_order')->where('status',2)->where('is_strength_sync',0)->where('finish_time',$now_time,'<=')->get(['gpo_id','order_num','user_id','is_robot'])->getResult();
        if(!empty($order_list)){
            foreach ($order_list as $order) {
                $is_pass = 1;
                write_log(2,"order_num:{$order['order_num']},is_robot:{$order['is_robot']}");
                if($order['is_robot'] == 0){
                    try {
                        $strengthRes = $this->userStrengthLogic->user_strength_open($order['user_id'], $order['order_num'], '', 1);
                        write_log(2,"实商开通结果:" . $strengthRes);
                        $is_pass = $strengthRes;
                    } catch (DbException $e) {
                        Log::info($e->getMessage());
                    } catch (MysqlException $e) {
                        Log::info($e->getMessage());
                    }
                }
                if($is_pass == 1){
                    $update['status'] = 2;
                    $update['is_strength_sync'] = 1;
                    $update['finish_time'] = time();
                    Query::table('sb_group_purchase_order')->where('gpo_id',$order['gpo_id'])->update($update)->getResult();
                }else{
                    write_log(2,"拼团结果执行失败:" . $order['order_num']);
                }
            }
        }
        Log::info('拼团实商同步任务结束');
        return ['拼团实商同步任务'];
    }

    /**
     * 判断拼团个数&自动开团
     * 7.2-7.4 每分钟第16秒执行
     * @Scheduled(cron="5 * * 2-4 7 *")
     * @throws MysqlException
     */
    public function robotOpenTask()
    {
        Log::info('机器人开团任务开启');
        $hour = date('H');
        $now_time = time();
        $start_cache = 'robot_open_time';
        $robot_purchase_cache = 'robot_purchase_cache';
        $original_list = Query::table('sb_group_purchase_order')->where('is_leader',1)->where('status',1)->count()->getResult();
        $has_defer = -1; //是否晚间顺延
        if($original_list < 6){
            Log::info('团个数:' . $original_list);
            $robot_user_list = 'robot_user_list';
            $start_time = 0;
            if($this->appRedis->exists($start_cache)){
                $start_time = $this->appRedis->get($start_cache);
            }
            Log::info('执行时间:' . date('Y-m-d H:i:s',$start_time));

            if($start_time > 0 && $start_time <= $now_time){
                if($hour < 8 || $hour > 21){
                    //参团时间顺延
                    $random = rand(30,60);
                    $random_time = time() + $random * 60;
                    $this->appRedis->set($start_cache,$random_time);
                    $has_defer = 1;
                }else{
                    if($this->appRedis->exists($robot_user_list)) {
                        $robot_info = $this->appRedis->lPop($robot_user_list);
                        $info = explode('#', $robot_info);
                        if (is_array($info)) {
                            $order_num = "AO" . date("ymdHis") . str_pad($info[0], 6, 0, STR_PAD_LEFT) . rand(10, 99);
                            $original_num = "ORN".date("ymdHis").str_pad($info[0],6,0,STR_PAD_LEFT).rand(10,99);
                            $order['user_id'] = (int)$info[0];
                            $order['original_num'] = $original_num;
                            $order['order_num'] = $order_num;
                            $order['is_robot'] = 1;
                            $order['is_leader'] = 1;
                            $order['add_time'] = $start_time;
                            $order['open_time'] = $start_time;
                            $order['pay_time'] = $start_time;
                            $order['status'] = 1;
                            $purchase_order = Query::table('sb_group_purchase_order')->insert($order)->getResult();
                            if($purchase_order){
                                //缓存机器人用户名/头像
                                $this->appRedis->hSet($robot_purchase_cache,$info[0],$robot_info);
                                write_log(2,'机器人开团订单号:' . $original_num);
                                $random = rand(60,180);
                                $random_time = time() + $random * 60;
                                $this->appRedis->hSet('group_purchase_robot_list',$original_num,$random_time);
                                $has_defer = 0;
                            }
                        }
                    }
                }
            }
        }
        if($has_defer == 0){
            $random = rand(30,60);
            $random_time = time() + $random * 60;
            $this->appRedis->set($start_cache,$random_time);
        }
        Log::info('机器人开团任务结束');
        return ['机器人开团任务'];
    }

    /**
     * 机器人自动参团
     * 7.2-7.4 每分钟第16秒执行
     * @Scheduled(cron="16 * * 2-5 7 *")
     * @throws MysqlException
     */
    public function robotMissionTask()
    {
        $hour = date('H');
        $now_time = time();
        Log::info('机器人参团任务开启');
        $cache_key = 'group_purchase_robot_list';
        $robot_user_list = 'robot_user_list';
        $robot_purchase_cache = 'robot_purchase_cache';
        $robot_list = $this->appRedis->hKeys($cache_key);
        if(!empty($robot_list)){
            foreach ($robot_list as $robot) {
                $open_time = $this->appRedis->hGet($cache_key,$robot);
                if($open_time <= $now_time){
                    if($hour < 8 || $hour > 21){
                        //参团时间顺延
                        $random = rand(60,180);
                        $random_time = time() + $random * 60;
                        $this->appRedis->hSet($cache_key,$robot,$random_time);
                    }else{
                        //执行自动参团
                        if($this->appRedis->exists($robot_user_list)){
                            $robot_info = $this->appRedis->lPop($robot_user_list);
                            $info = explode('#',$robot_info);
                            if(is_array($info)){
                                $order_num = "AO".date("ymdHis").str_pad($info[0],6,0,STR_PAD_LEFT).rand(10,99);
                                $order['user_id'] = (int)$info[0];
                                $order['original_num'] = $robot;
                                $order['order_num'] = $order_num;
                                $order['is_robot'] = 1;
                                $order['add_time'] = $open_time;
                                $order['pay_time'] = $open_time;
                                $order['status'] = 1;
                                $purchase_order = Query::table('sb_group_purchase_order')->insert($order)->getResult();
                                if($purchase_order != false){
                                    //缓存机器人用户名/头像
                                    $this->appRedis->hSet($robot_purchase_cache,$info[0],$robot_info);
                                    //其他参团人判断
                                    $original_order = Query::table('sb_group_purchase_order')->where('original_num',$robot)->where('status',1)->get(['gpo_id','is_robot','user_id','order_num'])->getResult();
                                    if(!empty($original_order) && count($original_order) >= 3) {
                                        $original_gpo_id = array_column($original_order, 'gpo_id');
                                        $all_purchase['status'] = 2;
                                        $all_purchase['finish_time'] = time();
                                        $update_all = Query::table('sb_group_purchase_order')->whereIn('gpo_id',$original_gpo_id)->update($all_purchase)->getResult();
                                        if($update_all){
                                            foreach ($original_order as $original) {
                                                if($original['is_robot'] == 0){
                                                    try {
                                                        $strengthRes = $this->userStrengthLogic->user_strength_open($original['user_id'], $original['order_num'], '', 1);
                                                        if($strengthRes == 1){
                                                            $upPurchase['is_strength_sync'] = 1;
                                                            $upPurchase['finish_time'] = time();
                                                            Query::table('sb_group_purchase_order')->where('gpo_id',$original['gpo_id'])->update($upPurchase)->getResult();
                                                            $this->send_purchase_notice($original['user_id'],3);
                                                        }
                                                        write_log(2,"实商开通结果:" . $original['order_num'] . '=>' . $strengthRes);
                                                    } catch (DbException $e) {
                                                        Log::info($e->getMessage());
                                                    } catch (MysqlException $e) {
                                                        Log::info($e->getMessage());
                                                    }
                                                }
                                            }
                                        }
                                        $this->appRedis->hDel($cache_key,$robot);
                                    }else{
                                        //机器人参团时间缓存
                                        if(!empty($original_order)){
                                            $robot_list = array_column($original_order,'is_robot');
                                            if(in_array(0,$robot_list)){
                                                $random = rand(30,90);
                                                foreach ($original_order as $key => $person) {
                                                    if($person['is_robot'] == 0){
                                                        $this->send_purchase_notice($person['user_id'],2);
                                                    }
                                                }
                                            }else{
                                                $random = rand(60,180);
                                            }
                                            $random_time = time() + $random * 60;
                                            $this->appRedis->hSet('group_purchase_robot_list',$robot,$random_time);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        Log::info('机器人参团任务结束');
        return ['机器人参团任务'];
    }

    /**
     * 拼团到期退款操作
     * 7.2-7.4 每分钟第25秒执行
     * @Scheduled(cron="25 * * 2-5 7 *")
     * @throws MysqlException
     * @throws DbException
     */
    public function cancelTask()
    {
        Log::info('拼团到期任务开启');
        $cache_key = 'group_purchase_robot_list';
        $prev_time = strtotime('-24 hour');
        $purchase_list = Query::table('sb_group_purchase_order')->where('open_time',$prev_time,'<=')->where('is_leader',1)->where('status',1)->get(['gpo_id','original_num'])->getResult();
        if(!empty($purchase_list)){
            $cancel = [];
            foreach ($purchase_list as $item) {
                write_log(2,'订单:' . $item['original_num'] . '拼团失败');
                $cancel[] = $item['original_num'];
            }
            if(!empty($cancel)){
                $update['status'] = 3;
                $update['cancel_time'] = time();
                $cancelRes = Query::table('sb_group_purchase_order')->whereIn('original_num',$cancel)->update($update)->getResult();
                if($cancelRes){
                    foreach ($cancel as $order_num) {
                        //查询到期订单信息
                        $order_list = Query::table('sb_group_purchase_order')->where('original_num',$order_num)->where('status',3)->get()->getResult();
                        if(!empty($order_list)){
                            foreach ($order_list as $order_info) {
                                if(!empty($order_info) && $order_info['is_robot'] == 0){
                                    $appreciation_order = Query::table('sb_appreciation_order')->where('order_num',$order_info['order_num'])->where('user_id',$order_info['user_id'])->where('status', 20)->one()->getResult();
                                    $order_record = Query::table('sb_order_record')->where('re_type',13)->where('order_uid',$order_info['user_id'])->where('order_num',$order_info['order_num'])->where('status',2)->one()->getResult();
                                    if($appreciation_order && $order_record){
                                        //退回金额到钱包
                                        Db::beginTransaction();
                                        $walletRes = $recordRes = $walletRec = false;
                                        $wallet = Query::table('sb_order_wallet')->where('user_id',$order_info['user_id'])->one(['balance'])->getResult();
                                        if(!empty($wallet)){
                                            write_log(2,'用户:' . $order_info['user_id'] . '钱包余额' . $wallet['balance']);
                                            $new_balance = $wallet['balance'] + $appreciation_order['pay_total_amount'];
                                            write_log(2,'用户:' . $order_info['user_id'] . '退回后金额' . $new_balance);
                                            $data['balance'] = $new_balance;
                                            $data['update_time'] = time();
                                            $walletRes = Query::table('sb_order_wallet')->where('user_id',$order_info['user_id'])->update($data)->getResult();
                                            //记录退回
                                            $ref['order_num'] = $order_info['order_num'];
                                            $ref['shop_id'] = $order_info['user_id'];
                                            $ref['user_id'] = $order_info['user_id'];
                                            $ref['re_price'] = $appreciation_order['pay_total_amount'];
                                            $ref['reason_name'] = '拼团失败退回';
                                            $ref['type'] = 1;
                                            $ref['re_type'] = 7;
                                            $ref['remark'] = '实商拼团失败，退回钱包余额';
                                            $ref['total_price'] = $appreciation_order['pay_total_amount'];
                                            $ref['status'] = 2;
                                            $ref['add_time'] = time();
                                            $ref['finish_time'] = time();
                                            $recordRes = Query::table('sb_order_refund')->insert($ref)->getResult();
                                            //钱包记录添加
                                            $rec['user_id'] = $order_info['user_id'];
                                            $rec['money'] = $appreciation_order['pay_total_amount'];
                                            $rec['order_num'] = $order_info['order_num'];
                                            $rec['record_from'] = 25;
                                            $rec['record_type'] = 1;
                                            $rec['record_time'] = time();
                                            $walletRec = Query::table('sb_order_wallet_record')->insert($rec)->getResult();
                                        }

                                        if($walletRes && $walletRec && $recordRes){
                                            Db::commit();
                                        }else{
                                            Db::rollback();
                                        }
                                    }
                                }
                            }
                        }
                        $this->appRedis->hDel($cache_key,$order_num);
                    }
                }
            }
        }
        Log::info('拼团到期任务结束');
        return ['拼团到期任务'];
    }

    /**
     * 拼团通知
     * @param $user_id
     * @param int $step 1:创建 2：新加入 3：成功
     */
    protected function send_purchase_notice($user_id,$step = 1)
    {
        $msgContent = $content = $sms = '';
        $sms_url = $this->userData->getSetting('group_purchase_short_url');
        switch ($step){
            case 1:
                $msgContent = '【拼团提醒】一年实力商家权益拼团创建成功！赶紧邀请好友参团吧！点击查看';
                $content = '【拼团提醒】一年实力商家权益拼团创建成功！赶紧邀请好友参团吧！#点击查看#';
                $sms = '【搜布】一年实力商家权益拼团创建成功！赶紧邀请好友参团吧！' .$sms_url. ' 退订回T';
                break;

            case 2:
                $msgContent = '【拼团提醒】您的拼团有新的好友加入，再邀请一位即可拼团成功！点击查看';
                $content = '【拼团提醒】您的拼团有新的好友加入，再邀请一位即可拼团成功！#点击查看#';
                $sms = '【搜布】您的拼团有新的好友加入，再邀请一位即可拼团成功！ ' . $sms_url . '退订回T';
                break;

            case 3:
                $msgContent = '【拼团提醒】恭喜您一年实力商家拼团成功！赶紧打开app享受权益吧！点击查看';
                $content = '【拼团提醒】恭喜您一年实力商家拼团成功！赶紧打开app享受权益吧！#点击查看#';
                $sms = '【搜布】恭喜您一年实力商家拼团成功！赶紧打开app享受权益吧！ ' . $sms_url . ' 退订回T';
                break;
        }

        if(!empty($msgContent)){
            //发送系统消息
            $config = \Swoft::getBean('config');
            $sys_msg = $config->get('sysMsg');
            $extra = $sys_msg;
            $extra['title'] =  $extra['msgTitle'] = "";
            $extra['isRich'] = 0;
            $extra['msgContent'] = $msgContent;
            $extra['content'] = $content;
            $d = [["keyword"=>"#点击查看#","type"=>18,"id"=>0,"url"=>$this->userData->getSetting('group_purchase_url')]];
            $datashow = array();
            $extra['data'] = $d;
            $extra['commendUser'] = array();
            $extra['showData'] = $datashow;
            $data['extra'] = $extra;
            sendInstantMessaging('1',(string)$user_id,json_encode($data['extra']));
        }
        if($this->userData->getSetting('SEND_SMS') == 1 && !empty($sms)){
            $user_info = $this->userData->getUserInfo($user_id);
            if(!empty($user_info)){
                sendSms($user_info['phone'],$sms,2,2);
            }
        }
    }
}
