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
use Swoft\Db\Exception\DbException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * 首次缴纳保证金用户统计任务
 *
 * @Task("SafePriceUser")
 * @package App\Tasks
 */
class SafePriceUserTask{

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;
    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    /**
     * 获取第一次缴纳保证金任务
     * 3-5 seconds per minute 每10分钟执行一次
     *
     * @Scheduled(cron="0 *\/10 * * * *")
     * @throws DbException
     */
    public function firstTask()
    {
        $cache_queue_list = 'safe_price_user_list';
        Log::info('首次缴纳保证金统计任务开始');
        //内部账号获取
        $agent_user = $this->userData->getTesters();
        //获取最近5天有缴纳的人
        $last_time = strtotime(date('Y-m-d',strtotime('-5 day')));
        $params = [
            'pay_status' => 2,
            ['pay_time', '>', $last_time],
            ['user_id','not in', $agent_user]
        ];
        $user_list = $this->userData->get_safe_price_uid_list($params);
        if(!empty($user_list)){
            if($this->redis->exists($cache_queue_list))
            {
                $this->redis->delete($cache_queue_list);
            }
            $safe_user_list = [];
            foreach ($user_list as $item) {
                $safe_user_list[$item['user_id']] = $item['pay_time'];
            }
            $safe_price_user_list = array_keys($safe_user_list);
            if(!empty($safe_price_user_list)){
                //判断保证金是否还在账户内
                $safe_price_user_ids = $this->userData->getUserByUids($safe_price_user_list,['user_id']);
                if(!empty($safe_price_user_ids)){
                    $user_ids = [];
                    foreach ($safe_price_user_ids as $safe_price_user_id) {
                        $user_ids[] = $safe_price_user_id['userId'];
                    }
                    //判断保证金缴纳次数
                    if(empty($user_ids))
                        return ['没有要执行的保证金判断'];
                    $safe_price_times = $this->userData->get_safe_price_ulist_times($user_ids);
                    if(!empty($safe_price_times)){
                        foreach ($safe_price_times as $safe) {
                            if($safe['count'] == 1){
                                $this->redis->zAdd($cache_queue_list,$safe_user_list[$safe['user_id']],$safe['user_id']);
                            }
                        }
                    }
                    $this->redis->expire($cache_queue_list,10*60);
                }
            }
        }
        Log::info('首次缴纳保证金统计任务结束');
        return ['首次缴纳保证金任务执行完成'];
    }
}
