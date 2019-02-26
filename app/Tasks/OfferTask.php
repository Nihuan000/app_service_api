<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Tasks;

use App\Models\Data\BuyData;
use App\Models\Data\OfferData;
use App\Models\Data\ProductData;
use App\Models\Data\UserData;
use Swoft\App;
 use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class OfferTask - define some tasks
 *
 * @Task("Offer")
 * @package App\Tasks
 */
class OfferTask{
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
     * @var ProductData
     */
    private $proData;

    /**
     * @Inject()
     * @var OfferData
     */
    private $offerData;

    private $limit = 500;

    /**
     * 商机推荐消息提醒发送, 每分钟第10秒执行
     * @author Nihuan
     * @Scheduled(cron="0 * * * * *")
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function SendQueueTask()
    {
        $date = date('Y-m-d');
        $index = '@OfferQueue_';
        $historyIndex = '@OfferQueueHistory_';
        $expire_time = 0;
        $len = $this->searchRedis->lLen($index . $date);
        if(!$this->searchRedis->exists($historyIndex . $date)){
            $expire_time = 604800;
        }
        $test_list = $this->userData->getTesters();
        $grayscale = getenv('IS_GRAYSCALE');
        if($len > 0){
            $config = \Swoft::getBean('config');
            $sys_msg = $config->get('offerMsg');
            $pages = ceil($len/$this->limit);
            for ($i=1;$i<=$pages;$i++){
                $list = $this->searchRedis->lrange($index . $date,0, $this->limit);
                if(!empty($list)){
                    foreach ($list as $item) {
                        $msg_arr = explode('#',$item);
                        $buy_id = (int)$msg_arr[0];
                        $offerer_id = (int)$msg_arr[1];
                        $pro_id = (int)$msg_arr[2];
                        $buyInfo = $this->buyData->getBuyInfo($buy_id);
                        $buyer = $this->userData->getUserInfo((int)$buyInfo['userId']);
                        $user_info = $this->userData->getUserInfo($offerer_id);
                        $pro_info = $this->proData->getProductInfo($pro_id);
                        $receive_status = 0;
                        if(($grayscale == 1 && in_array($offerer_id, $test_list)) || $grayscale == 0){
                            $receive_status = 1;
                        }
                        //队列当前内容删除
                        $this->searchRedis->lPop($index . $date);
                        //历史推送记录查询
                        if($this->searchRedis->exists($historyIndex . $date)){
                            $history = $this->searchRedis->sIsMember($historyIndex . $date, $item);
                        }else{
                            $history = false;
                        }
                        if($offerer_id != $buyer['user_id'] && $receive_status == 1 && in_array($user_info['role'],[2,3,4]) && $history == false){
                            //报价记录写入
                            $data['buy_id'] = $buy_id;
                            $data['user_id'] = $buyInfo['userId'];
                            $data['offerer_id'] = $offerer_id;
                            $data['offer_price'] = isset($pro_info['price']) ? sprintf('%.2f',$pro_info['price']) : '';
                            $data['units'] = isset($pro_info['unit']) ? $pro_info['unit'] : '';
                            $data['cut_price'] = isset($pro_info['cutPrice']) ? sprintf('%.2f',$pro_info['cutPrice']) : '';
                            $data['cut_units'] = isset($pro_info['cutUnits']) ? $pro_info['cutUnits'] : '';
                            $data['offer_content'] = '';
                            $data['status'] = 0;
                            $data['is_audit'] = 1;
                            $data['offer_time'] = $data['audit_time'] = time();
                            $data['offer_source'] = 19; //自动报价
                            $result = $this->offerData->saveOffer($data);
                            if($result){
                                //报价产品写入
                                $offer_pro_data['offer_id'] = $result;
                                $offer_pro_data['pro_id'] = $pro_id;
                                $offer_pro_data['add_time'] = $data['offer_time'];
                                $offer_pro_data['is_delete'] = 0;
                                $this->offerData->saveOfferProduct($offer_pro_data);
                                //发送系统消息
                                ################## 消息展示内容开始 #######################
                                $type = $data['status'];
                                $extra = $sys_msg;
                                $extra['type'] = 2;
                                $extra['id'] = $buy_id;
                                $extra['buy_id'] = $buy_id;
                                $extra['offer_id'] = $result;
                                $extra['image'] = !is_null($buyInfo['pic']) ? get_img_url($buyInfo['pic']): '';
                                $extra['name'] = $user_info['name'];
                                $extra['status'] = $type;
                                $extra['bigPrice'] = $data['offer_price'];
                                $extra['units'] = is_null($data['units']) ? '' : $data['units'];
                                $extra['cutPrice'] = (string)$data['cut_price'];
                                $extra['cut_units'] = is_null($data['cut_units']) ? '' : $data['cut_units'];
                                $extra['amount'] = $buyInfo['amount'];
                                $extra['unit'] = $buyInfo['unit'];
                                $extra['title'] = $buyInfo['remark'];
                                $extra['msgTitle'] = '收到报价';
                                $notice['extra'] = $extra;
                                $notice['extra']['msgContent'] = "{$user_info['name']}给您报价了！";
                                sendInstantMessaging('2', $buyInfo['userId'], json_encode($notice['extra']));   //发送通知
                                ################## 消息展示内容结束 #######################

                                ################## 消息基本信息开始 #######################
                                $extra['type'] = 1;
                                $extra['msgTitle'] = '自动报价成功';
                                $extra['msgContent'] = "已为您对采购[{$buyInfo['remark']}]自动报价!";
                                ################## 消息基本信息结束 #######################

                                $notice['extra'] = $extra;
                                sendInstantMessaging('2', (string)$offerer_id, json_encode($notice['extra']));

                                $this->searchRedis->sAdd($historyIndex . $date, $item);
                                if($expire_time > 0){
                                    $this->searchRedis->expire($historyIndex . $date,$expire_time);
                                }
                            }
                        }
                    }
                }
            }
        }
        return ['自动报价发送'];
    }
}
