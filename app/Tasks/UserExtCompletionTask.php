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
use App\Models\Data\UserExtData;
use App\Models\Logic\UserStrengthLogic;
use Swoft\App;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;
use Swoole\Mysql\Exception;

/**
 * 用户扩展信息补全任务
 *
 * @Task("UserExtCompletion")
 * @package App\Tasks
 */
class UserExtCompletionTask{

    /**
     * @Inject()
     * @var UserExtData
     */
    private $extData;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * 实商体验标识
     * @var string
     */
    private $experience_key = 'new_reg_user_receive';

    /**
     * 实商领取过期提醒标识
     * @var string
     */
    private $msg_key = 'finish_role_of_811_new_suppliers';

    /**
     * 实商领取缓存列表
     * @var string
     */
    private $cache_experience_list = 'experience_list_cache';

    /**
     * @Inject()
     * @var UserStrengthLogic
     */
    private $strengthLogic;

    /**
     * @Inject("appRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @param int $user_id
     * @param int $role
     * @return string
     * @throws MysqlException
     * @throws DbException
     */
    public function work(int $user_id, int $role)
    {
        Log::info("{$user_id}用户扩展信息补全开始");
        $extInfo = $this->extData->getExtInfo($user_id, $role);
        //钱包判断
        if(empty($extInfo['order_wallet'])){
            $wallet = [
                'user_id' => $user_id,
                'status' => 1,
                'update_time' => time(),
            ];
            $this->extData->addWallete($wallet);
        }
        //添加等级/积分日志
        if(empty($extInfo['user_score'])){
            $score = [
                'user_id' => $user_id,
                'add_time' => time(),
            ];
            if($role == 2){
                $score['score_value'] = 0;
                $score['base_score_value'] = 0;
                $score['level_id'] = 1;
                $score['level_name'] = '普通';
            }
            $this->extData->addScore($role,$score);
        }
        //添加默认地址
        if(empty($extInfo['user_address']) && !empty($extInfo['user_info'])){
            $address = [
                'user_id' => $user_id,
                'username' => (string)$extInfo['user_info']['name'],
                'phone' => $extInfo['user_info']['phone'],
                'province' => $extInfo['user_info']['province'],
                'province_id' => $extInfo['user_info']['provinceId'],
                'city' => $extInfo['user_info']['city'],
                'city_id' => $extInfo['user_info']['cityId'],
                'address' => $extInfo['user_info']['detailAddress'],
                'is_default' => 1,
                'addtime' => time(),
            ];
            $this->extData->addAddress($address);
        }
        //公司信息
        if(empty($extInfo['user_company'])){
            $company = [
                'user_id' => $user_id,
                'is_auto_reply' => 1,
                'reply_id' => 1,
                'add_time' => time(),
                'is_activated' => 1,
            ];

            $this->extData->addCompany($company);
        }
        //采购商删除订阅
        if($role == 1){
            //当前实商信息
            $user_strength_info = $this->userData->getUserStrengthInfo($user_id);
            //取消之前的实商体验
            if(!empty($user_strength_info) && $user_strength_info['pay_for_open'] == 0){
                $strength_info = [
                    'id' => $user_strength_info['id'],
                    'user_id' => $user_strength_info['user_id'],
                    'pay_for_open' => $user_strength_info['pay_for_open'],
                    'end_time' => $user_strength_info['end_time'],
                ];
                $this->strengthLogic->user_strength_expired($strength_info,0,1);
            }
            //删除店铺索引记录
            publicSearch(7,['shopId' => $user_id],$user_id);
        }
        //是否赠送实商
        if($role == 2){
            $can_receive = 0;
            $change_times = $this->extData->getRoleUpdateCount($user_id);
            $start_switch = $this->userData->getSetting('start_version_811');
            if($change_times == 1 && $start_switch == 1){
                $agent_device = $this->userData->getAgentDevice();
                //设备号判断
                $device_list = $this->extData->getUserDevice($extInfo['user_info']['device']);
                if($device_list == 0){
                    $can_receive = 1;
                }else if($device_list > 0){
                    //判断是否测试账号
                    if(in_array($extInfo['user_info']['device'],$agent_device)){
                        $can_receive = 1;
                    }
                }

                if($can_receive == 1){
                    Log::info("{$user_id}可以领取体验实商");
                    try {
                        //实商领取
                        $openRes = $this->strengthLogic->user_strength_open($user_id,'',$this->experience_key,0);
                        if($openRes == 1) {
                            //系统消息
                            $strength_delay_time = strtotime(date('Y-m-d 10:00:00', time() + 15 * 24 * 3600));
                            $this->redis->zAdd($this->msg_key,$strength_delay_time,$user_id);
                            Log::info("{$user_id}体验实商领取成功");
                        }
                    } catch (Exception $e){
                        Log::info("{$user_id}实商领取失败, Errno:" . $e->getCode() . ',Msg:' . $e->getMessage() . ',Error:' . $e->getTraceAsString());
                        $this->redis->rPush($this->cache_experience_list,$user_id);
                    }

                }
            }
        }
        //8.10 新注册的采购商且注册ｉｄ是单数的发送系统消息
        Log::info("{$user_id}用户扩展信息补全结束");
        return '补全任务执行';
    }

    /**
     * 体验实商任务
     * @Scheduled(cron="16 * * * * *")
     * @throws DbException
     * @throws MysqlException
     */
    public function strengthReceiveTask()
    {
        Log::info('用户体验实商任务开启');
        $queue_len = $this->redis->lLen($this->cache_experience_list);
        if($queue_len > 0){
            $user_id = $this->redis->lPop($this->cache_experience_list);
            if(!empty($queue)){
                Log::info('用户实商体验任务信息:' . $user_id);
                /* @var UserStrengthLogic $strength_logic */
                $strength_logic = App::getBean(UserStrengthLogic::class);
                $openRes = $strength_logic->user_strength_open($user_id,'',$this->experience_key,0);
                Log::info($user_id . '用户体验实商结果:' . $openRes);
                if($openRes == 1) {
                    //系统消息
                    $strength_delay_time = strtotime(date('Y-m-d 10:00:00', time() + 15 * 24 * 3600));
                    $this->redis->zAdd($this->msg_key,$strength_delay_time,$user_id);
                    Log::info("{$user_id}体验实商领取成功");
                }
            }
        }
        Log::info('用户体验实商任务结束');
    }
}
