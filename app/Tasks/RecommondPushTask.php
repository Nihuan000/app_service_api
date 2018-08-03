<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-26
 * Time: 下午2:40
 * Desc: 昨天未收到报价且未读的采购信息再次推送
 * Desc: 推送流程.
 * Desc: 获取昨日未报价采购信息
 * Desc: 获取每条采购的未读推送记录
 * Desc: 统计用户未读推送总次数/每条采购推送内容/用户关联采购生成
 * Desc: 根据未读总次数判断单个推还是批量推
 * Desc: 推送完成
 */

namespace App\Tasks;

use App\Models\Data\BuyData;
use App\Models\Data\TbPushBuyRecordData;
use App\Models\Data\UserData;
use Swoft\Bean\Annotation\Inject;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * RecommondPush task
 *
 * @Task("RecommondPush")
 */
class RecommondPushTask
{

    /**
     * @Inject()
     * @var TbPushBuyRecordData
     */
    private $pushBuyData;

    /**
     * @Inject()
     * @var BuyData
     */
    private $buyData;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**

     * @author Nihuan
     * @throws \Swoft\Db\Exception\DbException
     * @Scheduled(cron="0 0 7 * * *")
     */
    public function rePushTask()
    {
        $title = '精选采购推荐';
        $now_time = date('Y-m-d H:i:s');
        echo "############### 任务开始时间: {$now_time} ###############" . PHP_EOL;
        $day_time = date('Y-m-d',strtotime('-1 day'));
        echo '刷新日期:' . $day_time . PHP_EOL;
        $buyRes = $this->buyData->getNoQuoteBuy();
        if(!empty($buyRes)){
            $pushRecordStatic = []; //用户记录次数列表
            $pushContent = []; //推送内容列表
            $userPushBuy = []; //采购推送内容列表
            $uids = []; //总的用户列表
            foreach ($buyRes as $buy) {
                $pushRecord = $this->pushBuyData->getPushListById($buy['buyId']);
                if(!empty($pushRecord)){
                    foreach ($pushRecord as $record) {
                        $uids[] = $record['userId'];
                        $pushRecordStatic[$record['userId']] ++;
                        $userPushBuy[$record['userId']][] = $buy['buyId'];
                    }
                }
                $pic = get_img_url($buy['pic']);
                $content = [
                    'type' => 2,
                    'id' => (int) $buy['buyId'],
                    'link' => '',
                    'title' => $title,
                    'content' => (string)$buy['remark'],
                    'pic' => $pic
                ];
                $pushContent[$buy['buyId']]['simple'] = $content;
                $msg = '';
                if(!empty($buy['amount'])){
                    $msg .= "求购{$buy['amount']}{$buy['unit']};";
                }
                if(!empty($buy['remark'])){
                    $msg .= $buy['remark'];
                }
                $msg .= "...等>TOTAL<条精选采购等你接单，立即报价>>";
                $content['content'] = $msg;

                $pushContent[$buy['buyId']]['batch'] = $content;
            }

            $uids = array_unique($uids);
            $user_info = $this->userData->getUserByUids($uids,['user_id','cid']);
            foreach ($user_info as $info) {
                if($info['cid'] == ''){
                    continue;
                }
                $msg_list = [];
                if($pushRecordStatic[$info['userId']] > 3 ){
                    $buyId = current($userPushBuy[$info['userId']]);
                    $msg = $pushContent[$buyId]['batch'];
                    $msg['content'] = str_replace('>TOTAL<',$pushRecordStatic[$info['userId']],$msg);
                    $msg_list[] = $msg;
                }else{
                    foreach ($userPushBuy[$info['userId']] as $buyId) {
                        $msg = $pushContent[$buyId]['simple'];
                        $msg_list[] = $msg;
                    }
                }
                try {
                    foreach ($msg_list as $msg) {
                        get_message($info['cid'], $msg);
                        echo "采购{$msg['id']}推送给用户{$info['userId']}成功" . PHP_EOL;
                    }
                } catch (\Exception $e) {
                    print_r($e->getMessage());
                }
            }
        }
        return ['精选采购推荐'];
    }
}