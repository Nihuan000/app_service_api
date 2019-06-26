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

use App\Models\Logic\UserStrengthLogic;
use Swoft\Bean\Annotation\Inject;
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
     * 拼团成功实商补充任务/机器人参团状态修改
     * 7.2-7.4 每分钟第5秒执行
     * @Scheduled(cron="5 * * 26-28 6 *")
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
     * @Scheduled(cron="0 0 * 26-28 6 *")
     * @throws MysqlException
     */
    public function robotOpenTask()
    {
        Log::info('机器人参团任务开启');
        $hour = date('H');
        $now_time = time();
        $start_cache = 'robot_open_time';
        $robot_purchase_cache = 'robot_purchase_cache';
        $original_list = Query::table('sb_group_purchase_order')->where('is_leader',1)->where('status',1)->count()->getResult();
        $has_defer = 0; //是否晚间顺延
        if($original_list < 6){
            $robot_user_list = 'robot_user_list';
            $start_time = 0;
            if($this->appRedis->exists($start_cache)){
                $start_time = $this->appRedis->get($start_cache);
            }

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
        Log::info('机器人参团任务结束');
        return ['机器人参团任务'];
    }

    /**
     * 机器人自动参团
     * 7.2-7.4 每分钟第16秒执行
     * @Scheduled(cron="16 * * 26-28 6 *")
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
     * @Scheduled(cron="25 * * 26-28 6 *")
     */
    public function cancelTask()
    {
        Log::info('拼团到期任务开启');
        $prev_time = strtotime('-24 hour');
        $purchase_list = Query::table('sb_group_purchase_order')->where('open_time',$prev_time,'<=')->where('status',1)->get(['gpo_id','order_num','is_robot'])->getResult();
        if(!empty($purchase_list)){
            $cancel = [];
            foreach ($purchase_list as $item) {
                write_log(2,'订单:' . $item['order_num'] . '拼团失败');
                $cancel[] = $item['order_num'];
            }
            if(!empty($cancel)){
                $update['status'] = 3;
                $update['cancel_time'] = time();
                Query::table('sb_group_purchase_order')->whereIn('order_num',$cancel)->update($update)->getResult();
            }
        }
        Log::info('拼团到期任务结束');
        return ['拼团到期任务'];
    }
}
