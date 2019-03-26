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
use Swoft\Bean\Annotation\Inject;
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

    private $queue_key = 'msg_queue_list';

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $msgRedis;

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
     * @Scheduled(cron="0 30 15 * * *")
     */
    public function strengthExpNotice()
    {
        $notice_history_key = 'notice_strength_history'; //提示历史记录
        $last_time = strtotime(date('Y-m-d',strtotime('+7 day')));
        $params = [
            ['end_time',$last_time,'<='],
            'is_expire' => 0,
            'pay_for_open' => 1
        ];
        $strength_list = $this->userData->getWillExpStrength($params,['user_id']);
        if(!empty($strength_list)){
            $config = \Swoft::getBean('config');
            $sys_msg = $config->get('sysMsg');
            foreach ($strength_list as $strength) {
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
                    $msg_body = [
                        'fromId' => '1',
                        'targetId' => $strength['userId'],
                        'msgExtra' => $notice['extra'],
                        'timedTask' => 0
                    ];
                    $this->msgRedis->rPush($this->queue_key,json_encode($msg_body));
                    $this->redis->sAdd($notice_history_key, $strength['userId']);
                    $user_ids[] = $strength['userId'];
                }
            }
            if(!empty($user_ids)){
                write_log(2,json_encode($user_ids));
            }
        }
        return ['实商续费提醒已发送'];
    }
}