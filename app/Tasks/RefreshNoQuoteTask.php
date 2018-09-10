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
    private $min_clicks = 100;
    private $min_offer = 3;

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
     * 8小时无报价采购刷新
     * @author Nihuan
     * @Scheduled(cron="0 * * * * *")
     */
    public function getHandleTask()
    {
        $prev_time = strtotime('-3 day');
        $last_time = strtotime('-1 day');
        $refresh_prev = strtotime('-3 hour');
        $now_time = date('Y-m-d H:i:s');
        $buy_res = Db::query("select b.buy_id from sb_buy t left join sb_buy_attribute ba ON ba.buy_id = t.buy_id WHERE t.add_time > {$prev_time} AND t.add_time <= {$last_time} AND t.refresh_time <= {$refresh_prev} AND t.is_audit = 0 AND t.clicks < {$this->min_clicks} AND ba.offer_count < {$this->min_offer} ORDER BY buy_id ASC")->getResult();
        if(!empty($buy_res)){
            $refresh_count = 0;
            foreach ($buy_res as $buy) {
                if($buy['buy_id']%2 == 0){
                    continue;
                }else{
                    //写入刷新队列
                    $this->redis->rpush($this->buy_queue_key,$buy['buy_id']);
                    $this->redis->sAdd($this->refresh_queue_key,$buy['buy_id']);
                    $refresh_count += 1;
                }
            }
            echo "[$now_time] 共刷新采购条数:" . $refresh_count . PHP_EOL;
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
            return;
        }else{
            $buy_id = $this->redis->lpop($this->buy_queue_key);
            $buy_info = $this->buyData->getBuyInfo($buy_id);
            if($buy_info['clicks'] >= $this->min_clicks){
                return;
            }
            $offer_info = $this->attrData->getByBid($buy_id);
            if($offer_info['offer_count'] >= $this->min_offer){
                return;
            }
            $up_result = $this->buyData->updateBuyInfo($buy_id,['refresh_time'=> time(),'alter_time' => time()]);
            if($up_result){
                echo "采购 {$buy_id} 刷新修改" . PHP_EOL;
                if($this->redis->sIsMember($this->refresh_queue_key,$buy_id)){
                    $this->redis->sRem($this->refresh_queue_key,$buy_id);
                }
                $event = [
                    'event' => 'SoubuApp_API_TaskBuy_RefreshTask',
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
}