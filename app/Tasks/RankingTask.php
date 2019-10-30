<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 2019/10/23
 * Time: 下午2:40
 * Desc: 排行榜类任务
 */

namespace App\Tasks;

use App\Models\Data\OfferData;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\DbException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class RankingTask
 *
 * @Task("RankingTask")
 * @package App\Tasks
 */
class RankingTask
{
    /**
     * @Inject("appRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject()
     * @var OfferData
     */
    private $offerData;

    /**
     * @var int
     */
    private $limit_days = 7;

    /**
     * @var
     */
    private $offer_cache_list = 'offer_ranking_list_';

    /**
     * 报价排行榜
     * @Scheduled(cron="03 0 01 * * *")
     * @throws DbException
     */
    public function offerRankingTask()
    {
        $date = date('Y_m_d');
        Log::info($date . '日报价排行榜任务开始');
        $cache_list = $this->offer_cache_list . $date;
        //删除历史记录
        if($this->redis->has($cache_list)){
            $this->redis->delete($cache_list);
        }
        $start_time = strtotime(date('Y-m-d',strtotime("-{$this->limit_days} day")));
        $end_time = strtotime(date('Y-m-d'));
        $real_end_date = strtotime(date('Y-m-d',strtotime('-1 day')));
        $limit = 100;
        $offerer_list = $this->offerData->getOffererListByTime($start_time,$end_time,$limit);
        if(!empty($offerer_list)){
            foreach ($offerer_list as $item) {
                $this->redis->zAdd($cache_list,$item['offer_count'],$item['offerer_id']);
            }
        }
        if($this->redis->has($cache_list)){
            $this->redis->expire($cache_list, $this->limit_days * 24 * 3600);
            $this->redis->set('offer_ranking_date',json_encode(['start' => $start_time, 'end' => $real_end_date]));
            $this->redis->delete('ranking_list_cache');
        }
        Log::info($date . '日报价排行榜任务结束');
        return '报价排行榜';
    }

    /**
     * 报价排行榜
     * @Scheduled(cron="0 0 10 * * *")
     */
    public function offerRankingMsgTask()
    {

    }
}
