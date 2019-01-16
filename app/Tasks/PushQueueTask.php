<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 * 商机推荐推送执行任务队列，后台审核后推送到当前队列，任务判断索引数据是否已存在，存在则推送，不存在等待下次执行
 */

namespace App\Tasks;

use App\Models\Logic\ElasticsearchLogic;
use Swoft\Bean\Annotation\Inject;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class PushQueueTask - define some tasks
 *
 * @Task("PushQueue")
 * @package App\Tasks
 */
class PushQueueTask{

    /**
     * @Inject()
     * @var ElasticsearchLogic
     */
    protected $esLogic;


    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    public $redis;

    /**
     * A cronTab task
     * 3-5 seconds per minute 每分钟第3-5秒执行
     *
     * @Scheduled(cron="*\/10 * * * * *")
     */
    public function PushProcessTask()
    {
        $date = date('Y-m-d');
        $index = '@RecommendPushQueue_';
        $historyIndex = '@RecommendPushHistory_';
        $len = $this->redis->lLen($index . $date);
        if($len > 0){
            $item = $this->redis->lPop($index . $date);
            $buy_info = json_decode($item,true);
            $params = [
                'buy_id' => (int)$buy_info['buy_id'],
                'title' => (string)$buy_info['title'],
                'content' => (string)$buy_info['content'],
                'pic' => (string)$buy_info['pic']
            ];
            //判断当前记录是否已推送
            $historyRes = $historyExists = $this->redis->sIsMember($historyIndex . $date, $params['buy_id']);
            if($historyRes == true){
                Log::info("记录已存在: {$params['buy_id']} - {$params['content']}");
            }else{
                $pushRes = $this->esLogic->checkDataExists($params);
                if($pushRes == 1){
                    Log::info("消息已推送: {$params['buy_id']} - {$params['content']}");
                }elseif($pushRes == 2){
                    Log::info("索引记录不存在，等待下次推送: {$params['buy_id']} - {$params['content']}");
                }else{
                    Log::info("其他状态: {$params['buy_id']} - {$params['content']}");
                }
            }
        }
        return ['商机推荐队列任务执行'];
    }
}
