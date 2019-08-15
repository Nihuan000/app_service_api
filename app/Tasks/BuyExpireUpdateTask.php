<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-9-4
 * Time: 下午3:31
 * Desc: 过期采购状态修改
 */

namespace App\Tasks;
use App\Models\Data\ProductData;
use App\Models\Data\UserData;
use App\Models\Entity\Buy;
use App\Models\Entity\BuyAttribute;
use Swoft\Bean\Annotation\Inject;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * BuyExpireUpdate task
 *
 * @Task("BuyExpireUpdate")
 */
class BuyExpireUpdateTask
{

    /**
     * @Inject()
     * @var ProductData
     */
    protected $productData;

    /**
     * @Inject()
     * @var UserData
     */
    protected $userData;

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    protected $redis;

    /**
     * 待推送队列
     * @var string
     */
    private $wait_push_list = 'wait_push_buy_list_';


    /**
     * 过期采购状态修改
     * @author Nihuan
     * @Scheduled(cron="0 * * * * *")
     */
    public function expireBuyTask()
    {
        $time = time();
        $now_time = date('Y-m-d H:i:s');
        $buyRes = Buy::findAll([
            ['expire_time','<=',$time],
            ['expire_time','>',0],
            'is_audit' => 0,
            'del_status' => 1,
            'status' => 0
        ],
            ['fields' => ['buy_id']])->getResult();
        if(!empty($buyRes)){
            $buy_ids= [];
            foreach ($buyRes as $buy) {
                $buy_ids[] = $buy['buyId'];
            }
            echo "[$now_time] 采购:" . json_encode($buy_ids) . '已过期' . PHP_EOL;
            if(!empty($buy_ids)){
                Buy::updateAll(['alter_time' => time(), 'status' => 2],['buy_id' => $buy_ids])->getResult();
            }
        }
        sleep(1);
        return ['过期采购状态修改'];
    }

    /**
     * 过期推广产品状态修改
     * @return array
     * @Scheduled(cron="30 * * * * *")
     */
    public function expireSearchKeyword()
    {
        $now_time =time();
        $expire_ids = [];
        $expire_record = $this->productData->getExpireKeyPro();
        if(!empty($expire_record)){
            foreach ($expire_record as $item) {
                $expire_ids[] = $item['id'];
            }
            $params = [
                'ids' => $expire_ids,
                'status' => 0
            ];
            echo "[$now_time] 推广产品:" . json_encode($expire_ids) . '已过期' . PHP_EOL;
            $this->productData->updateExpirePro($params,['status' => 1]);
        }
        return ['过期推广产品状态修改'];
    }

    /**
     * 即将到期实商系统消息提醒
     * @return array
     * @Scheduled(cron="0 0 11 * * *")
     * @throws \Swoft\Db\Exception\DbException
     */
    public function strengthExpNotice()
    {
        $notice_history_key = 'notice_strength_history_' . date('Y'); //提示历史记录
        $last_time = strtotime(date('Y-m-d',strtotime('+7 day')));
        $prev_time = strtotime(date('Y-m-d',strtotime('+6 day')));
        $params = [
            ['end_time', '<=',$last_time],
            ['end_time', '>=',$prev_time],
            'is_expire' => 0,
            'pay_for_open' => 1
        ];
        $strength_list = $this->userData->getWillExpStrength($params,['user_id']);

        $test_list = $this->userData->getTesters();
        if(!empty($strength_list)){
            $user_ids = [];
            $config = \Swoft::getBean('config');
            $sys_msg = $config->get('sysMsg');
            foreach ($strength_list as $strength) {
                $receive_status = 0;
                if(in_array($strength['userId'], $test_list) || $this->userData->getSetting('strength_over_switch') == 1){
                    $receive_status = 1;
                }
                if($receive_status == 1){
                    $history_record = $this->redis->sIsMember($notice_history_key,(string)$strength['userId']);
                    if($history_record == 0){
                        //发送系统消息
                        ################## 消息基本信息开始 #######################
                        $extra = $sys_msg;
                        $extra['title'] = '实商即将到期';
                        $extra['msgContent'] = "您的实力商家权限即将到期，\n点击续费";
                        ################## 消息基本信息结束 #######################

                        ################## 消息扩展字段开始 #######################
                        $extraData['keyword'] = '#点击续费#';
                        $extraData['type'] = 18;
                        $extraData['url'] = $this->userData->getSetting('user_strength_url');
                        ################## 消息扩展字段结束 #######################

                        $extra['data'] = [$extraData];
                        $extra['content'] = "您的实力商家权限即将到期，#点击续费#";
                        $notice['extra'] = $extra;
                        $this->redis->sAdd($notice_history_key, (string)$strength['userId']);
                        sendInstantMessaging('1', (string)$strength['userId'], json_encode($notice['extra']));
                        $user_ids[] = $strength['userId'];
                    }else{
                        write_log(2,$strength['userId'] . '推送记录已存在');
                    }
                }
            }
            if(!empty($user_ids)){
                write_log(2,json_encode($user_ids));
            }
        }
        return ['实商续费提醒已发送'];
    }

    /**
     * 即将到期采购信息提醒
     * @return string
     * @Scheduled(cron="36 * * * * *")
     */
    public function willExpireBuyTask()
    {
        $hour = date('H');
        $start_time = strtotime('+6 hour');
        $end_time = $start_time - 59;
        Log::info('即将到期采购任务开始');
        $buyRes = Buy::findAll([
            ['expire_time','<=',$start_time],
            ['expire_time','>',$end_time],
            'is_audit' => 0,
            'del_status' => 1,
            'status' => 0
        ],
            ['fields' => ['buy_id','user_id','expire_time']])->getResult();
        if(!empty($buyRes)){
            $buy_ids = $cache_list = [];
            $push_cache_key = 'buy_expire_push_history_';
            foreach ($buyRes as $buy) {
                $can_send = 1;
                //报价数获取
                $buy_attr = BuyAttribute::findOne(['buy_id' => $buy['buyId']],['fields' => ['offer_count']])->getResult();
                if(isset($buy_attr) && $buy_attr['offerCount'] > 5){
                    write_log(2,"采购" . $buy['buyId'] . "报价数大于5条");
                    $can_send = 0;
                }
                $has_pushed = $this->redis->exists($push_cache_key . date('Y-m-d'));
                $push_list = $this->redis->zRevRange($push_cache_key . date('Y-m-d'),0, -1,true);
                if(isset($push_list[$buy['userId']])){
                    $time_differ = $push_list[$buy['userId']] - $buy['expireTime'];
                    if($time_differ < 2 * 3600 && $time_differ > -2 * 3600){
                        write_log(2,"采购" . $buy['buyId'] . "2小时内已存在推送记录");
                        $can_send = 0;
                    }
                }else{
                    $this->redis->zAdd($push_cache_key . date('Y-m-d'),$buy['expireTime'],$buy['userId']);
                    if($has_pushed == false){
                        $this->redis->expire($push_cache_key . date('Y-m-d'), 24 * 3600);
                    }
                }

                if($can_send == 1){
                    $buy_ids[] = $buy['userId'];
                    $cache_list[] = $buy['userId'] . '@' . $buy['buyId'] . '@' . $buy_attr['offerCount'];
                }
            }

            if(!empty($buy_ids)){
                write_log(2,"采购" . json_encode($buy_ids) . "即将在6小时后过期");
                if($hour > 21 || $hour < 8){
                    //写入待推送缓存
                    $next_day = $hour > 21 ? date('Y_m_d',strtotime('+1 day')) : date('Y_m_d');
                    $this->redis->rPush($this->wait_push_list . $next_day, 'buy_expire#' . json_encode($cache_list));
                    write_log(2,'不在可发送时间内，写入采购待推送队列');
                }else{
                    //发送推送
                    $msg = $this->buy_info_msg();
                    sendInstantMessaging('1',$buy_ids,$msg,1);
                }
            }
        }
        Log::info('即将到期采购任务结束');
        return '即将到期采购执行';
    }

    /**
     * 缓存队列消息发送
     * 每天上午10点执行
     *
     * @Scheduled(cron="0 0 10 * * *")
     */
    public function BuyCacheQueueTask()
    {
        $date = date('Y_m_d');
        Log::info('采购过期缓存队列任务开始');
        if($this->redis->exists($this->wait_push_list . $date)){
            $push_list = $this->redis->lRange($this->wait_push_list . $date,0,-1);
            if(!empty($push_list)){
                foreach ($push_list as $push) {
                    $msg_info = explode('#',$push);
                    if(is_array($msg_info)){
                        switch ($msg_info[0]){
                            case 'buy_expire':
                                $buy_ids = json_decode($msg_info[1],true);
                                if(!empty($buy_ids)){
                                    foreach ($buy_ids as $cache) {
                                        $push_info = explode('@',$cache);
                                        if(count($push_info) > 1){
                                            $user_id = (int)$push_info[0];
                                            //获取采购信息
                                            $buy_info = Buy::findOne(['buy_id' => $push_info[1]],['status'])->getResult();
                                            $has_offer = (int)$push_info[2] > 0 ? 1 : 0;
                                            $is_expire = $buy_info['status'] == 2 ? 1 : 0;
                                            $msg = $this->buy_info_msg($is_expire, $has_offer);
                                            sendInstantMessaging('1',$user_id,$msg,0);
                                        }
                                    }
                                }
                                break;
                        }
                    }
                    $this->redis->zRem($this->wait_push_list . $date,$push);
                }
            }
        }
        Log::info('采购过期缓存队列任务结束');
        return '采购过期缓存队列任务';
    }

    /**
     * 采购商消息
     * @param int $is_expire
     * @param int $has_offer
     * @return false|string
     */
    private function buy_info_msg($is_expire = 0, $has_offer = 0)
    {
        $config = \Swoft::getBean('config');
        $extra =  $config->get('sysMsg');
        $extra['isRich'] = 0;
        if($is_expire == 0){
            $title = '采购即将到期';
            $msg = '您发布的采购信息即将到期，请前往刷新或修改有效期。';
            $keyword = '点击前往';
        }else{
            $title = '采购已到期';
            $msg = '您发布的采购信息已到期，如需继续找布，请前往';
            $keyword = '再次找布';
        }
        $extra['title'] =  $extra['msgTitle'] = $title;
        $extra['msgContent'] = $msg . $keyword;
        $extra['content'] = $msg . "#{$keyword}#";
        $d = [["keyword"=>"#{$keyword}#","type"=>29,"id"=>0,"url"=> '', 'showOffer' => $has_offer]];
        $data_show = array();
        $extra['data'] = $d;
        $extra['commendUser'] = array();
        $extra['showData'] = $data_show;
        $data['extra'] = $extra;

        return json_encode($data['extra']);
    }
}