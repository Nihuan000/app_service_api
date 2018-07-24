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
     * 商机推荐消息提醒发送
     * @author Nihuan
     * @Scheduled(cron="*\/10 * * * * *")
     */
    public function RecommendQueueTask()
    {
        $date = date('Y-m-d');
        $index = '@RecommendMsgQueue_';
        $len = $this->searchRedis->lLen($index . $date);
        if($len > 0){
            $config = \Swoft::getBean('config');
            $invitate_offer = $config['msgTemplate.offerSms.invitate_offer'];
            $offer_msg = $config['msgTemplate.offerMsg'];
            $pages = ceil($len/$this->limit);
            for ($i=1;$i<=$pages;$i++){
                $list = $this->searchRedis->lrange($index . $date,0, $this->limit);
                if(!empty($list)){
                    foreach ($list as $item) {
                        $msg_arr = explode('#',$item);
                        $user_id = (int)$msg_arr[0];
                        $buy_id = (int)$msg_arr[1];
                        $buyInfo = $this->buyData->getBuyInfo($buy_id);
                        $buyer = $this->userData->getUserInfo($buyInfo['user_id']);
                        $user_info = $this->userData->getUserInfo($user_id);

                        $phone = $user_info['phone'];
                        $sms_content = str_replace('>NAME<',trim($buyer['name']),$invitate_offer);
                        sendSms($phone, $sms_content, 2, 1);

                        //发送系统消息
                        $extra = $offer_msg;
                        $extra['type'] = 1;
                        $extra['id'] = $buy_id;
                        $extra['buy_id'] = $buy_id;
                        $extra['offer_id'] = 0;
                        $extra['image'] = !is_null($buyInfo['pic']) ? get_img_url($buyInfo['pic']) : '';
                        $extra['name'] = $buyer['name'];
                        $extra['amount'] = $buyInfo['amount'];
                        $extra['unit'] = $buyInfo['unit'];
                        $extra['title'] = $buyInfo['remark'];
                        $extra['msgTitle'] = '收到邀请';
                        $notice['extra'] = $extra;
                        $notice['extra']['msgContent'] = "买家{$buyer['name']}邀请您为他报价！";
                        sendInstantMessaging('2', (string)$user_id, json_encode($notice['extra']));
                        $this->searchRedis->lPop($index . $date);
                    }
                }
            }
        }
    }
}