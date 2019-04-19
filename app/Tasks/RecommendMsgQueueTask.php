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
use App\Models\Data\UserData;
use Swoft\Bean\Annotation\Inject;
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

    private $limit = 500;

    /**
     * 商机推荐消息提醒发送, 每分钟第10秒执行
     * @author Nihuan
     * @Scheduled(cron="10 * * * * *")
     * @throws \Swoft\Db\Exception\DbException
     */
    public function RecommendQueueTask()
    {
        $date = date('Y-m-d');
        $index = '@RecommendMsgQueue_';
        $historyIndex = '@RecommendMsgHistory_';
        $len = $this->searchRedis->lLen($index . $date);
        $test_list = $this->userData->getTesters();
        $is_send_offer = $this->userData->getSetting('recommend_deposit_switch');
        $grayscale = getenv('IS_GRAYSCALE');
        if($len > 0){
            $config = \Swoft::getBean('config');
            $invitate_offer = $config->get('offerSms.invitate_offer');
            $sys_msg = $is_send_offer==1 ? $config->get('offerMsg') : $config->get('sysMsg');
            $pages = ceil($len/$this->limit);
            $pages = 1;
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
                        if(($grayscale == 1 && in_array($user_id, $test_list)) || $grayscale == 0){
                            $receive_status = 1;
                        }
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
                            $phone = $user_info['phone'];
                            $sms_content = str_replace('>NAME<',trim($buyer['name']),$invitate_offer);
                            sendSms($phone, $sms_content, 2, 1);

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