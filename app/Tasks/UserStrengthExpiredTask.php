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
use Swoft\App;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\DbException;
use Swoft\Log\Log;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * 实力商家过期处理任务
 *
 * @Task("UserStrengthExpired")
 * @package App\Tasks
 */
class UserStrengthExpiredTask{

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * 实商过期任务
     * 3-5 seconds per minute 每分钟第45秒执行
     *
     * @Scheduled(cron="45 * * * * *")
     * @throws DbException
     */
    public function expiredTask()
    {
        Log::info('实商过期任务开启');
        $now_time = time();
        $params = [
            'is_expire' => 0,
            ['end_time','<=',$now_time]
        ];
        $expire_list = $this->userData->getWillExpStrength($params,['id','user_id','pay_for_open','end_time']);
        if(!empty($expire_list)){
            /* @var UserStrengthLogic $strength_logic */
            $strength_logic = App::getBean(UserStrengthLogic::class);
            foreach ($expire_list as $item) {
                $strength_info = [
                    'id' => $item['id'],
                    'user_id' => $item['userId'],
                    'pay_for_open' => $item['payForOpen'],
                    'end_time' => $item['endTime'],
                ];
                $expired_result = $strength_logic->user_strength_expired($strength_info);
                if($expired_result == 1){
                    Log::info('用户' . $item['userId'] . '实商已过期');
                }
            }
        }else{
            Log::info('没有要执行的实商记录');
        }
        Log::info('实商过期任务结束');
        return ['实商到期任务'];
    }
}
