<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-17
 * Time: 下午1:56
 * Desc: 商机推荐提醒消息任务
 */

namespace App\Tasks;

use App\Models\Data\BuyData;
use App\Models\Data\BuyRelationTagData;
use App\Models\Data\UserData;
use App\Models\Data\UserSubscriptionTagData;
use App\Models\Logic\UserLogic;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\DbException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * RecommendMsgQueue task
 *
 * @Task("RecommendMsgQueue")
 */
class RecommendMsgQueueTask
{

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $searchRedis;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @Inject()
     * @var BuyData
     */
    private $buyData;

    /**
     * @Inject()
     * @var BuyRelationTagData
     */
    private $buyRelationData;

    /**
     * @Inject()
     * @var UserSubscriptionTagData
     */
    private $userRelationData;

    /**
     * @Inject()
     * @var UserLogic
     */
    private $userLogic;

    private $limit = 500;

    /**
     * 商机推荐消息提醒发送, 每分钟第10秒执行
     * @return array
     * @author Nihuan
     * @Scheduled(cron="10 * * * * *")
     */
    public function RecommendQueueTask()
    {
        $date = date('Y-m-d');
        $index = '@RecommendMsgQueue_';
        $historyIndex = '@RecommendMsgHistory_';
        $len = $this->searchRedis->lLen($index . $date);
        $is_send_offer = $this->userData->getSetting('recommend_deposit_switch');
        if($len > 0){
            $config = \Swoft::getBean('config');
            $sys_msg = $is_send_offer==1 ? $config->get('offerMsg') : $config->get('sysMsg');
            $pages = ceil($len/$this->limit);
            for ($i=1;$i<=$pages;$i++){
                $list = $this->searchRedis->lrange($index . $date,0, $this->limit);
                if(!empty($list)){
                    foreach ($list as $item) {
                        $msg_arr = explode('#',$item);
                        $user_id = (int)$msg_arr[0];
                        $buy_id = (int)$msg_arr[1];
                        $buyInfo = $this->buyData->getBuyInfo($buy_id);
                        $buyer = $this->userData->getUserInfo((int)$buyInfo['userId']);
                        $user_info = $this->userData->getUserInfo($user_id);
                        $receive_status = 0;
                        //队列当前内容删除
                        $this->searchRedis->lPop($index . $date);
                        //历史推送记录查询
                        if($this->searchRedis->exists($historyIndex . $date)){
                            $history = $this->searchRedis->sIsMember($historyIndex . $date, (string)$item);
                        }else{
                            $history = false;
                        }
                        if($user_id != $buyer['user_id'] && $receive_status == 1 && in_array($user_info['role'],[2,3,4]) && $history == false){
                            $this->searchRedis->sAdd($historyIndex . $date, (string)$item);

                            //发送系统消息
                            if($is_send_offer == 1){
                                ################## 消息展示内容开始 #######################
                                $extra = $sys_msg;
                                $extra['image'] = !is_null($buyInfo['pic']) ? get_img_url($buyInfo['pic']) : '';
                                $extra['type'] = $buyInfo['status'];
                                $extra['id'] = $buy_id;
                                $extra['buy_id'] = $buy_id;
                                $extra['name'] = $buyer['name'];
                                $extra['title'] = (string)$buyInfo['remark'];
                                $extra['amount'] = $buyInfo['amount'];
                                $extra['unit'] = $buyInfo['unit'];
                                ################## 消息展示内容结束 #######################

                                ################## 消息基本信息开始 #######################
                                $extra['msgTitle'] = '收到邀请';
                                $extra['msgContent'] = "买家{$buyer['name']}邀请您为他报价！";
                                ################## 消息基本信息结束 #######################

                                $notice['extra'] = $extra;
                                sendInstantMessaging('11', (string)$user_id, json_encode($notice['extra']));
                            }else{
                                ################## 消息展示内容开始 #######################
                                $buy_info['image'] = !is_null($buyInfo['pic']) ? get_img_url($buyInfo['pic']) : '';
                                $buy_info['type'] = 1;
                                $buy_info['title'] = (string)$buyInfo['remark'];
                                $buy_info['id'] = $buy_id;
                                $buy_info['price'] = isset($buyInfo['price']) ? $buyInfo['price'] : "";
                                $buy_info['amount'] = $buyInfo['amount'];
                                $buy_info['unit'] = $buyInfo['unit'];
                                $buy_info['url'] = '';
                                ################## 消息展示内容结束 #######################

                                ################## 消息基本信息开始 #######################
                                $extra = $sys_msg;
                                $extra['title'] = '收到邀请';
                                $extra['msgContent'] = "买家{$buyer['name']}邀请您为他报价！\n查看详情";
                                $extra['commendUser'] = [];
                                $extra['showData'] = empty($buy_info) ? [] : [$buy_info];
                                ################## 消息基本信息结束 #######################

                                ################## 消息扩展字段开始 #######################
                                $extraData['keyword'] = '#查看详情#';
                                $extraData['type'] = 1;
                                $extraData['id'] = (int)$buy_id;
                                $extraData['url'] = '';
                                ################## 消息扩展字段结束 #######################

                                $extra['data'] = [$extraData];
                                $extra['content'] = "买家{$buyer['name']}邀请您为他报价！\n#查看详情#";
                                $notice['extra'] = $extra;
                                sendInstantMessaging('1', (string)$user_id, json_encode($notice['extra']));
                            }
                        }
                    }
                }
            }
        }
        return ['商机推荐消息提醒发送'];
    }

    /**
     * 发布采购45分钟未报价且供应商30分钟有登录消息
     * 每分钟执行
     * @Scheduled(cron="23 * * * * *")
     * @throws DbException
     */
    public function quotaNotReceiveTask()
    {
        Log::info('发布采购45分钟未报价且供应商30分钟有登录任务开始');
        $receive_msg_cache = 'ReceiveMsgCache_';
        $date = date('Y_m_d');
        $now_time = time();
        $start_time = strtotime(date('Y-m-d H:i',strtotime('-45 minute')));
        $end_time = $start_time + 59;
        $params = [
            ['add_time','between',$start_time,$end_time],
            'is_audit' => 0,
            'status' => 0
        ];
        $fields =['buy_id','pic','status','remark','amount','unit','user_id','add_time'];
        $buy_list = $this->buyData->getBuyList($params,$fields);
        if(!empty($buy_list)){
            $grayscale = getenv('IS_GRAYSCALE');
            $test_list = $this->userData->getTesters();
            foreach ($buy_list as $item) {
                $user_ids = [];
                if(date('H',$now_time) > 23 || date('H',$now_time) < 8){
                    continue;
                }
                $buy_id = $item['buyId'];
                if(($grayscale == 1 && !in_array($item['userId'], $test_list))){
                    continue;
                }
                $tag_params = [
                    'buy_id' => $item['buyId'],
                    ['top_id','>',100]
                ];
                $buy_top_ids = $this->buyRelationData->getBuyTagByParams($tag_params,['top_id']);
                $top_ids = [];
                if(!empty($buy_top_ids)){
                    foreach ($buy_top_ids as $buy_top_id) {
                        $top_ids[] = $buy_top_id['topId'];
                    }
                    if(!empty($top_ids)){
                        $user_ids = $this->userRelationData->getTagRelationUserIds($top_ids);
                    }
                }
                $last_user_ids = [];
                if(!empty($user_ids)){
                    if($grayscale == 1){
                        $user_ids = array_intersect($user_ids,$test_list);
                    }
                    //过滤当天已发送
                    $receive_history = $this->searchRedis->zRange($receive_msg_cache . $date,0,-1);
                    $arr_intersect = array_intersect($user_ids,$receive_history);
                    if(!empty($arr_intersect)){
                        $user_ids = array_diff($user_ids,$arr_intersect);
                    }
                    //30分钟内有登陆判断
                    $userParams = [
                        ['last_time','>', $now_time - 1800],
                        'user_id' => $user_ids
                    ];
                    $last_login_list = $this->userData->getUserDataByParams($userParams,2000);
                    if(!empty($last_login_list)){
                        foreach ($last_login_list as $user_id) {
                            $last_user_ids[] = (string)$user_id['userId'];
                        }
                    }
                }
                if(!empty($last_login_list)){
                    write_log(2,'45_minute_msg_user_id:' . json_encode($last_user_ids));
                    $buyer_info = $this->userData->getUserInfo($item['userId']);
                    $config = \Swoft::getBean('config');
                    $sys_msg = $config->get('offerMsg');
                    $pages = ceil(count($last_user_ids)/$this->limit);
                    for ($i=0;$i<$pages;$i++)
                    {
                        $offset = $i * $this->limit;
                        $list = array_slice($last_user_ids,$offset,$this->limit);
                        Log::info('send_list:' . json_encode($list));
                        ################## 消息展示内容开始 #######################
                        $extra = $sys_msg;
                        $extra['image'] = !is_null($item['pic']) ? get_img_url($item['pic']) : '';
                        $extra['type'] = $item['status'];
                        $extra['id'] = $buy_id;
                        $extra['buy_id'] = $buy_id;
                        $extra['name'] = (string)$buyer_info['name'];
                        $extra['title'] = (string)$item['remark'];
                        $extra['amount'] = $item['amount'];
                        $extra['unit'] = $item['unit'];
                        ################## 消息展示内容结束 #######################

                        ################## 消息基本信息开始 #######################
                        $extra['msgTitle'] = '收到邀请';
                        $extra['msgContent'] = "买家{$buyer_info['name']}邀请您为他报价！";
                        ################## 消息基本信息结束 #######################

                        $notice['extra'] = $extra;
                        sendInstantMessaging('11', $list, json_encode($notice['extra']),1);
                        $cache_list = [];
                        foreach ($list as $uid) {
                            $cache_list[] = $i;
                            $cache_list[] = $uid;
                        }
                        call_user_func_array([$receive_msg_cache . $date,'zadd'],$cache_list);
                    }
                }
            }
        }
        Log::info('发布采购45分钟未报价且供应商30分钟有登录任务结束');
        return ["发布采购45分钟未报价任务"];
    }


    /**
     * 商机推荐提醒记录清除, 每天6点执行，删除7天以前的记录
     * @author Nihuan
     * @Scheduled(cron="0 0 6 * * *")
     */
    public function RecommendHistoryExpireTask()
    {
        $date = date('Y-m-d',strtotime('-7 day'));
        $historyIndex = '@RecommendMsgHistory_';
        if($this->searchRedis->exists($historyIndex . $date)){
            $res = $this->searchRedis->delete($historyIndex . $date);
        }else{
            $res = true;
        }
        return ["删除商机推荐[{$date}]发送历史记录:{$res}"];
    }
}
