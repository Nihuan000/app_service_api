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

use App\Models\Logic\ScoreLogic;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\DbException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class UserScoreTask - define some tasks
 *
 * @Task("UserScore")
 * @package App\Tasks
 */
class UserScoreTask{

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject()
     * @var ScoreLogic
     */
    private $scoreLogic;

    /**
     * @var string
     */
    private $score_queue_key = 'score_queue_list';

    /**
     * 用户积分变更操作
     * 每分钟26秒执行一次
     * @Scheduled(cron="26 * * * * *")
     * @throws DbException
     */
    public function cronTask()
    {
//        Log::info('用户积分变更任务开启');
        $queue_len = $this->redis->lLen($this->score_queue_key);
        if($queue_len > 0){
            $queue = $this->redis->lPop($this->score_queue_key);
            if(!empty($queue)){
                Log::info('用户积分任务信息:' . $queue);
                $scoreRes = 0;
                $score_info = json_decode($queue,true);
                switch ($score_info['score_type']){
                    //加分
                    case 'increase':
                        $scoreRes = $this->scoreLogic->user_score_increase($score_info['user_id'],$score_info['scenes'],$score_info['extended']);
                        break;

                    //减分
                    case 'deduction':
                        $scoreRes = $this->scoreLogic->user_score_deduction($score_info['user_id'],$score_info['scenes'],$score_info['extended']);
                        break;
                }
                Log::info('用户积分执行结果:' . $scoreRes);
            }
        }
//        Log::info('用户积分变更任务结束');
    }


    /**
     * 用户积分变更操作
     * @param array $score_info
     * @throws DbException
     */
    public function scoreSyncTask(array $score_info)
    {
//        Log::info('用户积分变更任务开启');
        $queue = json_encode($score_info);
        Log::info('用户积分任务信息:' . $queue);
        $scoreRes = 0;
        switch ($score_info['score_type']){
            //加分
            case 'increase':
                $scoreRes = $this->scoreLogic->user_score_increase($score_info['user_id'],$score_info['scenes'],$score_info['extended']);
                break;

            //减分
            case 'deduction':
                $scoreRes = $this->scoreLogic->user_score_deduction($score_info['user_id'],$score_info['scenes'],$score_info['extended']);
                break;
        }
        Log::info('用户积分执行结果:' . $scoreRes);
        if($scoreRes == 0){
            $this->redis->rPush($this->score_queue_key,$queue);
        }
//        Log::info('用户积分变更任务结束');
    }
}
