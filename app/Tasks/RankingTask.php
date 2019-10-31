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
use App\Models\Data\UserData;
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
     * @Inject()
     * @var UserData
     */
    private $userData;

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
            $params = [
                'post_params' => [
                    'user_id' => env('TEST_USER_ID'),
                    'scene' => 'auto',
                ],
                'url' => env('API_BASE_URL') . '/frontend/offer-ranking'
            ];
            //缓存生成
            CURL($params);
        }
        Log::info($date . '日报价排行榜任务结束');
        return '报价排行榜';
    }

    /**
     * 报价排行榜消息发送
     * @Scheduled(cron="0 0 10 * * *")
     */
    public function offerRankingMsgTask()
    {
        $send_history = 'offer_ranking_send_history';
        $date = date('Y_m_d');
        Log::info($date . '报价排行榜消息发送任务开始');
        $cache_list = 'ranking_list_cache';
        if($this->redis->has($cache_list)){
            $list = $this->redis->get($cache_list);
            if(!empty($list)){
                $list_arr = json_decode($list,true);
                $user_list = array_splice($list_arr,0,20);
                foreach ($user_list as $item) {
                    //检测是否已发送过
                    $is_send = $this->redis->sIsmember($send_history,$item['user_id']);
                    if($is_send){
                        Log::info($item['user_id'] . '已存在发送历史');
                        continue;
                    }
                    $msg = $this->offer_ranking_msg($item['sort']);
                    $msgRes = sendInstantMessaging('1',$item['user_id'],$msg);
                    if($msgRes !== false){
                        $this->redis->sAdd($send_history,$item['user_id']);
                    }
                }
            }
        }
        Log::info($date . '报价排行榜消息发送任务结束');
        return '消息发送成功';
    }

    /**
     * 采购商消息
     * @param int $sort
     * @return false|string
     */
    private function offer_ranking_msg($sort = 0)
    {
        $url = $this->userData->getSetting('offer_ranking_url');
        if($sort > 20 || $sort < 1 || empty($url)){
            return false;
        }
        $config = \Swoft::getBean('config');
        $extra =  $config->get('sysMsg');
        $extra['isRich'] = 0;
        $extra['title'] =  $extra['msgTitle'] = "报价排行榜";
        $msg = "恭喜您目前处于报价排行榜第{$sort}名，";
        $extra['msgContent'] = $msg . "\n点击查看榜单";
        $extra['content'] = $msg . "#点击查看榜单#";
        $d = [["keyword"=>"#点击查看#","type"=>18,"id"=>0,"url"=> $url]];
        $data_show = array();
        $extra['data'] = $d;
        $extra['commendUser'] = array();
        $extra['showData'] = $data_show;
        $data['extra'] = $extra;

        return json_encode($data['extra']);
    }
}
