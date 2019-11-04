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
 * @Task("VisitRecord")
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
     * 访问记录异步任务
     * @param int $type
     * @param array $data
     * @return string
     * @throws MysqlException
     */
    public function syncVisitTask(int $type, array $data)
    {
        Log::info('异步访问记录任务');
        $recordRes = $this->logic->event_record($type,$data);
        if(!$recordRes){
            $visit_info = $type . '#' . json_encode($data);
            $this->redis->rPush($this->record_cache_list,$visit_info);
        }
        return '异步访问记录任务';
    }


    /**
     * 访问记录任务
     * @Scheduled(cron="5 * * * * *")
     * @throws MysqlException
     */
    public function userVisitTask()
    {
        Log::info('访问记录任务开始');
        //删除历史记录
        if($this->redis->has($this->record_cache_list)){
            $list = $this->redis->lRange($this->record_cache_list,0,20);
            if(!empty($list)){
                foreach ($list as $item){
                    $recordRes = false;
                    if(!empty($item)){
                        $record_arr = explode('#',$item);
                        $type = isset($record_arr[0]) && in_array($record_arr[0],[1,2,3]) ? (int)$record_arr[0] : 0;
                        $data = json_decode($record_arr[1],true);
                        if(is_array($data)){
                            $recordRes = $this->logic->event_record($type,$data);
                        }
                        if(!$recordRes){
                            $this->redis->rPush($this->record_cache_list,$item);
                        }else{
                            $this->redis->lPop($item);
                        }
                    }
                }
            }
        }
        Log::info('访问记录更新结束');
        return '访问记录任务';
    }
}
