<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-25
 * Time: 下午5:19
 * Desc: 发布8小时无报价采购刷新
 */

namespace App\Tasks;

use App\Models\Data\BuyAttributeData;
use App\Models\Data\BuyData;
use App\Models\Entity\Buy;
use App\Models\Logic\BuriedLogic;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Db;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * RefreshNoQuote task
 *
 * @Task("RefreshNoQuote")
 */
class RefreshNoQuoteTask
{
    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    /**
     * 刷新采购队列
     * @var string
     */
    private $buy_queue_key = 'refresh_buy_queue';
    private $refresh_queue_key = 'buy_queue_record';
    private $refresh_history_key = 'refresh_buy_history'; //采购刷新历史
    private $min_clicks = 100; //最低浏览数
    private $min_offer = 3; //最低报价数

    /**
     * @Inject()
     * @var buyData
     */
    private $buyData;

    /**
     * @Inject()
     * @var BuyAttributeData
     */
    private $attrData;

    /**
     * @Inject()
     * @var BuriedLogic
     */
    private $buriedLogic;

    /**
     * 3小时无报价采购刷新
     * @author Nihuan
     * @Scheduled(cron="0 * * * * *")
     */
    public function getHandleTask()
    {
        $prev_time = strtotime('-3 day');
        $last_time = strtotime('-1 day');
        $refresh_prev = strtotime('-3 hour');
        $now_time = date('Y-m-d H:i:s');
        $buy_res = Db::query("select t.buy_id from sb_buy t left join sb_buy_attribute ba ON ba.buy_id = t.buy_id WHERE t.add_time > {$prev_time} AND t.add_time <= {$last_time} AND t.refresh_time <= {$refresh_prev} AND t.is_audit = 0 AND t.status = 0 AND t.del_status = 1 AND t.clicks < {$this->min_clicks} AND ba.offer_count < {$this->min_offer} ORDER BY t.buy_id ASC")->getResult();
        if(!empty($buy_res)){
            $refresh_count = 0;
            foreach ($buy_res as $buy) {
                if($buy['buy_id']%2 == 0){
                    continue;
                }else{
                    $history_record = $this->get_refresh_history($buy['buy_id']);
                    if($history_record == 0){
                        //写入刷新队列
                        if(!$this->redis->sIsMember($this->refresh_queue_key,$buy['buy_id'])){
                            $this->redis->rpush($this->buy_queue_key,$buy['buy_id']);
                            $this->redis->sAdd($this->refresh_queue_key,$buy['buy_id']);
                            $refresh_count += 1;
                        }
                    }
                }
            }
            echo "[$now_time] 写入采购总条数:" . $refresh_count . PHP_EOL;
        }
        return ['无报价采购队列写入'];
    }


    /**
     * 报价队列采购刷新
     * @author Nihuan
     * @Scheduled(cron="0 *\/2 * * * *")
     */
    public function refreshTask()
    {
        $hour = date('H');
        if($hour > 10 || $hour < 6){
            return true;
        }else{
            $buy_id = $this->redis->lpop($this->buy_queue_key);
            if($buy_id == false){
                return true;
            }
            $buy_info = $this->buyData->getBuyInfo($buy_id);
            if(empty($buy_info)){
                return true;
            }
            if($buy_info['clicks'] >= $this->min_clicks){
                return true;
            }
            $offer_info = $this->attrData->getByBid($buy_id);
            if($offer_info['offer_count'] >= $this->min_offer){
                return true;
            }
            $up_result = $this->buyData->updateBuyInfo($buy_id,['refresh_time'=> time(),'alter_time' => time()]);
            if($up_result){
                echo "采购 {$buy_id} 刷新修改" . PHP_EOL;
                if($this->redis->sIsMember($this->refresh_queue_key,$buy_id)){
                    $this->redis->sRem($this->refresh_queue_key,$buy_id);
                }
                //写入发送历史
                $refresh_history_key = $this->refresh_history_key . date('Y-m-d');
                $history_exists = $this->redis->exists($refresh_history_key);
                $this->redis->sAdd($refresh_history_key,$buy_id);
                if(!$history_exists){
                    $expire_time = strtotime("+7 day");
                    $this->redis->expire($refresh_history_key,$expire_time - time());
                }
                $event_code = explode('_','SoubuApp_API_TaskBuy_RefreshTask');
                $event = [
                    'event' => $event_code,
                    'user_id' => 0,
                    'properties' =>[
                        'BuyId' => $buy_id,
                        'OperationTime' => time()
                    ]
                ];
                $this->buriedLogic->event_analysis($event);
            }
            return ['无报价采购刷新'];
        }
    }

    /**
     * 判断是否已存在历史刷新
     * @param $buy_id
     * @return int
     */
    private function get_refresh_history($buy_id)
    {
        $refresh_history = 0;
        for($k = 0; $k< 3; $k++){
            $history_date = date('Y-m-d',strtotime("- {$k} day"));
            $history_cache_key = $this->refresh_history_key . $history_date;
            if($this->redis->exists($history_cache_key)){
                if($this->redis->sIsMember($history_cache_key,$buy_id)){
                    $refresh_history = 1;
                    continue;
                }
            }
        }
        return $refresh_history;
    }
}