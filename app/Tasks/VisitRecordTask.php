<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 2019/10/23
 * Time: 下午2:40
 * Desc: 访问记录类任务
 */

namespace App\Tasks;

use App\Models\Logic\ClickLogic;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\MysqlException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class VisitRecordTask
 *
 * @Task("VisitRecordTask")
 * @package App\Tasks
 */
class VisitRecordTask
{
    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject()
     * @var ClickLogic
     */
    private $logic;


    /**
     * @var
     */
    private $record_cache_list = 'visit_record_list';

    /**
     * 访问记录任务
     * @Scheduled(cron="10 * * * * *")
     * @throws MysqlException
     */
    public function userVisitTask()
    {
        Log::info('访问记录任务开始');
        //删除历史记录
        if($this->redis->has($this->record_cache_list)){
            $info = $this->redis->lRange($this->record_cache_list,0,20);
            if(!empty($info)){
                foreach ($info as $item) {
                    $recordRes = false;
                    if(!empty($item)){
                        $record_arr = explode('#',$item);
                        $type = isset($record_arr[0]) && in_array($record_arr[0],[1,2,3]) ? (int)$record_arr[0] : 0;
                        $data = json_decode($record_arr[1],true);
                        if(is_array($data)){
                            $recordRes = $this->logic->event_record($type,$data);
                        }
                    }
                    if($recordRes){
                        $this->redis->lPop($this->record_cache_list);
                    }
                }
            }
        }
        Log::info('访问记录更新结束');
        return '访问记录任务';
    }
}
