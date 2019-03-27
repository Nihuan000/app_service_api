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
use Swoft\Bean\Annotation\Value;
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
     * @Inject ("demoRedis")
     * @var Redis
     */
    private $msgRedis;

    /**
     * 刷新采购队列
     * @var string
     */
    private $buy_queue_key = 'refresh_buy_queue';
    private $refresh_queue_key = 'buy_queue_record';
    private $norefresh_key = 'no_refresh_queue_record';
    private $refresh_history_key = 'refresh_buy_history'; //采购刷新历史
    private $notice_history_key = 'notice_history_list'; //提示刷新历史记录
    private $queue_key = 'msg_queue_list';
    private $min_clicks = 100; //最低浏览数
    private $min_offer = 3; //最低报价数
    private $notice_sec = 3600; //刷新提醒时间判断
    private $error_accuracy = 70; //误差精度 s

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
        $no_refresh_cache = $this->norefresh_key . '_' . date('Y_m_d');
        $prev_time = strtotime('-3 day');
        $refresh_prev = strtotime('-3 hour');
        $now_time = date('Y-m-d H:i:s');
        $buy_res = Db::query("select t.buy_id from sb_buy t left join sb_buy_attribute ba ON ba.buy_id = t.buy_id WHERE t.add_time > {$prev_time} AND t.refresh_time <= {$refresh_prev} AND t.is_audit = 0 AND t.status = 0 AND t.del_status = 1 AND t.clicks < {$this->min_clicks} AND ba.offer_count < {$this->min_offer} ORDER BY t.buy_id ASC")->getResult();
        if(!empty($buy_res)){
            $refresh_count = 0;
            foreach ($buy_res as $buy) {
                if($buy['buy_id']%2 == 0){
                    if(!$this->redis->sIsMember($no_refresh_cache,(string)$buy['buy_id'])){
                        $this->redis->sAdd($no_refresh_cache,$buy['buy_id']);
                    }
                    continue;
                }else{
                    $history_record = $this->get_refresh_history($buy['buy_id']);
                    if($history_record == 0){
                        //写入刷新队列
                        if(!$this->redis->sIsMember($this->refresh_queue_key,(string)$buy['buy_id'])){
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
        if($hour > 22 || $hour < 6){
            return true;
        }else{
            $msg = '';
            $refresh_status = 1;
            $buy_id = $this->redis->lpop($this->buy_queue_key);
            if($buy_id == false){
                $msg = '空采购id,跳过';
                $refresh_status = 0;
            }
            $buy_info = $this->buyData->getBuyInfo($buy_id);
            if(empty($buy_info)){
                $msg = '采购信息为空,跳过';
                $refresh_status = 0;
            }
            if($buy_info['status'] != 0){
                $msg = '采购信息已找到,跳过';
                $refresh_status = 0;
            }
            if($buy_info['clicks'] >= $this->min_clicks){
                $msg = "采购浏览数大于{$this->min_clicks},跳过";
                $refresh_status = 0;
            }
            $offer_info = $this->attrData->getByBid($buy_id);
            if($offer_info['offer_count'] >= $this->min_offer){
                $msg = "采购报价数大于{$this->min_offer},跳过";
                $refresh_status = 0;
            }
            if($refresh_status == 1){
                $up_result = $this->buyData->updateBuyInfo($buy_id,['refresh_time'=> time(),'alter_time' => time()]);
                if($up_result){
                    echo "采购 {$buy_id} 刷新修改" . PHP_EOL;
                    if($this->redis->sIsMember($this->refresh_queue_key,(string)$buy_id)){
                        $this->redis->sRem($this->refresh_queue_key,(string)$buy_id);
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
                    $msg = '采购信息已刷新';
                }
            }
            return [$msg];
        }
    }


    /**
     * 报价队列采购刷新
     * @author Nihuan
     * @Scheduled(cron="0 * * * * *")
     */
    public function sendRefreshNoticeTask()
    {
        $buy_ids = [];
        $date = date('Y-m-d');
        $audit_last = time() - $this->notice_sec;
        $audit_prev = time() - $this->error_accuracy - $this->notice_sec;
        $buy_res = Db::query("select t.user_id,t.buy_id,t.pic,t.remark,t.amount,t.unit from sb_buy t left join sb_buy_attribute ba ON ba.buy_id = t.buy_id WHERE t.audit_time < {$audit_last} AND t.audit_time >= {$audit_prev}  AND t.refresh_time <= {$audit_last} AND t.is_audit = 0 AND t.status = 0 AND t.del_status = 1 AND ba.offer_count = 0 ORDER BY t.buy_id ASC")->getResult();
        if(!empty($buy_res)){
            $config = \Swoft::getBean('config');
            $sys_msg = $config->get('sysMsg');
            $has_history = $this->redis->exists($this->notice_history_key . $date);
            foreach ($buy_res as $buy) {
                $history_record = $this->get_notice_history($buy['buy_id']);
                if($history_record == 0){
                    //发送系统消息
                    ################## 消息展示内容开始 #######################
                    $buy_info['image'] = !is_null($buy['pic']) ? get_img_url($buy['pic']) : '';
                    $buy_info['type'] = 1;
                    $buy_info['title'] = (string)$buy['remark'];
                    $buy_info['id'] = $buy['buy_id'];
                    $buy_info['price'] = "";
                    $buy_info['amount'] = $buy['amount'];
                    $buy_info['unit'] = $buy['unit'];
                    $buy_info['url'] = '';
                    ################## 消息展示内容结束 #######################

                    ################## 消息基本信息开始 #######################
                    $extra = $sys_msg;
                    $extra['title'] = '还未收到报价?您可以';
                    $extra['msgContent'] = "还没有收到报价？小布建议您重新编辑完善您的采购信息，供应商报价会更积极！ \n点击前往编辑";
                    $extra['commendUser'] = [];
                    $extra['showData'] = empty($buy_info) ? [] : [$buy_info];
                    ################## 消息基本信息结束 #######################

                    ################## 消息扩展字段开始 #######################
                    $extraData['keyword'] = '#点击前往编辑#';
                    $extraData['type'] = 1;
                    $extraData['id'] = (int)$buy['buy_id'];
                    $extraData['url'] = '';
                    ################## 消息扩展字段结束 #######################

                    $extra['data'] = [$extraData];
                    $extra['content'] = "还没有收到报价？小布建议您重新编辑完善您的采购信息，供应商报价会更积极！ #点击前往编辑#";
                    $notice['extra'] = $extra;
                    $this->redis->sAdd($this->notice_history_key . $date, $buy['buy_id']);
                    if($has_history == false){
                        $this->redis->expire($this->notice_history_key . $date,7*24*3600);
                    }
                    sendInstantMessaging('1', (string)$buy['user_id'], json_encode($notice['extra']));
                    $buy_ids[] = $buy['buy_id'];
                }
            }
            if(!empty($buy_ids)){
                write_log(2,json_encode($buy_ids));
            }
        }
        return [json_encode($buy_ids)];
    }

    /**
     * 发送提醒记录获取
     * @param $buy_id
     * @return int
     */
    private function get_notice_history($buy_id)
    {
        $has_record = 0;
        $date = date('Y-m-d');
        $history_list = $this->redis->exists($this->notice_history_key . $date);
        if($history_list){
            $history = $this->redis->sIsMember($this->notice_history_key,(string)$buy_id);
            if($history){
                $has_record = 1;
            }
        }
        return $has_record;
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
            $history_date = date('Y-m-d',strtotime("-{$k} day"));
            $history_cache_key = $this->refresh_history_key . $history_date;
            if($this->redis->exists($history_cache_key)){
                if($this->redis->sIsMember($history_cache_key,(string)$buy_id)){
                    $refresh_history += 1;
                }
            }
        }
        return $refresh_history;
    }
}