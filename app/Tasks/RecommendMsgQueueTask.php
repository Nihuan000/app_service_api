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

    private $limit = 1000;

    /**
     * 商机推荐消息提醒发送, 每分钟第10秒执行
     * @author Nihuan
     * @Scheduled(cron="10 * * * * *")
     */
    public function RecommendQueueTask()
    {
        $date = date('Y-m-d');
        $index = '@RecommendMsgQueue_';
        $len = $this->searchRedis->lLen($index . $date);
        if($len > 0){
            $config = \Swoft::getBean('config');
            $invitate_offer = $config->get('offerSms.invitate_offer');
            $sys_msg = $config->get('sysMsg');
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
                        if($user_id != $buyer['user_id']){
                            $phone = $user_info['phone'];
                            $sms_content = str_replace('>NAME<',trim($buyer['name']),$invitate_offer);
                            sendSms($phone, $sms_content, 2, 1);

                            //发送系统消息
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
                            $extra['content'] = $extra['msgContent'] = "买家{$buyer['name']}邀请您为他报价！\n查看详情";
                            $extra['commendUser'] = [];
                            $extra['showData'] = empty($buy_info) ? [] : [$buy_info];
                            ################## 消息基本信息结束 #######################

                            ################## 消息扩展字段开始 #######################
                            $extraData['keyword'] = '#查看详情#';
                            $extraData['type'] = 1;
                            $extraData['id'] = (int)$buy_id;
                            $extraData['url'] = '';
                            ################## 消息扩展字段结束 #######################

                            $extra['data'] = $extraData;
                            $notice['extra'] = $extra;
                            $notice['content'] = "买家{$buyer['name']}邀请您为他报价！\n#查看详情#";
                            sendInstantMessaging('1', (string)$user_id, json_encode($notice['extra']));
                        }
                        $this->searchRedis->lPop($index . $date);
                    }
                }
            }
        }
    }
}