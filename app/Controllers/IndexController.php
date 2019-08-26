<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Controllers;

use App\Models\Data\BuyData;
use App\Models\Data\ProductData;
use App\Models\Data\UserData;
use App\Models\Logic\ElasticsearchLogic;
use App\Models\Logic\UserLogic;
use App\Models\Logic\WechatLogic;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Message\Server\Request;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;
use Swoft\App;
use Swoft\Core\Coroutine;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Log\Log;
use Swoft\View\Bean\Annotation\View;
use Swoft\Contract\Arrayable;
use Swoft\Http\Server\Exception\BadRequestException;
use Swoft\Http\Message\Server\Response;
use ProductAI;

/**
 * Class IndexController
 * @Controller()
 */
class IndexController
{
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
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $msgRedis;

    private $queue_key = 'msg_queue_list';

    /**
     * @Inject()
     * @var ProductData
     */
    private $proData;

    /**
     * @Inject()
     * @var WechatLogic
     */
    private $wechatLogic;

    /**
     * @RequestMapping("/")
     * @View(template="index/index")
     * @return array
     */
    public function index(): array
    {
        $name = 'Framework';
        $notes = [
            'New Generation of PHP Framework',
            'Hign Performance, Coroutine and Full Stack'
        ];
        $links = [
        ];
        // 返回一个 array 或 Arrayable 对象，Response 将根据 Request Header 的 Accept 来返回数据，目前支持 View, Json, Raw
        return compact('name', 'notes', 'links');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function offer_test(Request $request)
    {
        $msg_arr = explode('#',$request->post('msg_record'));
        $user_id = (int)$msg_arr[0];
        $buy_id = (int)$msg_arr[1];
        $buyInfo = $this->buyData->getBuyInfo($buy_id);
        $buyer = $this->userData->getUserInfo((int)$buyInfo['userId']);
        $is_send_offer = env('SEND_OFFER_NOTICE');
        $setting_info = $this->userData->getSetting('recommend_deposit_switch');
        Log::info($setting_info);
        $config = \Swoft::getBean('config');
        $sys_msg = $is_send_offer==1 ? $config->get('offerMsg') : $config->get('sysMsg');

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
            $sendRes = sendInstantMessaging('11', (string)$user_id, json_encode($notice['extra']));
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
            $sendRes = sendInstantMessaging('1', (string)$user_id, json_encode($notice['extra']));
        }
        $name = '未知结果';
        if($sendRes){
            $name = '发送成功';
        }
        return compact('name');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function strengthExpTest(Request $request)
    {
        $user_id = $request->post('user_id');
        $notice_history_key = 'notice_strength_history'; //提示历史记录
        $last_time = strtotime(date('Y-m-d',strtotime('+7 day')));
        $params = [
            'user_id' => $user_id,
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


    /**
     * @param Request $request
     * @return array
     */
    public function strengthOverTest(Request $request)
    {
        $user_id = $request->post('user_id');
        $notice_history_key = 'over_strength_history'; //提示历史记录
        $last_time = strtotime(date('Y-m-d',strtotime('+7 day')));
        $params = [
            'user_id' => $user_id,
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

    /**
     * @param Request $request
     * @return array
     * @throws DbException
     */
    public function supplier_test(Request $request)
    {
        $user_id = $request->post('user_id');
        if(empty($user_id)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $last_days = date('Y-m-d',strtotime("-7 day"));
            $last_day_time = strtotime($last_days);
            if($last_days > 0){
                $params = [
                    ['last_time','>=', $last_day_time],
                    'user_id' => $user_id
                ];
                /* @var UserLogic $user_logic */
                $user_logic = App::getBean(UserLogic::class);
                $user_logic->supplierDataList($params, $last_day_time, 50);
            }
            $code = 200;
            $result = ['list' => []];
            $msg = '获取成功';
        }
        return compact('code','result','msg');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function malong_test(Request $request)
    {
        $img_url = $request->post('img_url');
        if(empty($img_url)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $search_match = [];
            $product_ai = new ProductAI\API(env('MALONG_ACCESS_ID'),env('MALONG_SECRET_KEY'));
            $response = $product_ai->searchImage('search',env('MALONG_SERVICE_ID'),get_img_url($img_url),[],[],50);
            if($response != false && $response['is_err'] == 0){
                foreach ($response['results'] as $result) {
                    $search_match[] = $result;
                }
            }
            $code = 0;
            $result = ['search_match' => $search_match];
            $msg = '请求成功';
        }

        return compact('code','result','msg');
    }

    /**
     * @return array|string
     */
    public function supplier_msg_test()
    {
        $send_cover = $this->userData->getSetting('supplier_data_cover');//报告图片
        $last_time = strtotime(date('Y-m-d'));
        $condition = [
            ['record_time','>=',$last_time],
            'send_status' => 0,
            'user_id' => 0,
            'send_time' => 0
        ];
        $count = $this->userData->getSupplierCount($condition);//未发送报告数
        if($count > 0){
            $last_id = 0;
            $send_user_id = [];//发送成功记录
            $no_send_user_id = [];//无需发送记录
                $params = [
                    ['sds_id','>',$last_id]
                ];
                $condition[] = $params;
                $list = $this->userData->getSupplierData($condition,1);

                if(empty($list))  return '';

                $url = $this->userData->getSetting('supplier_data_url');

                foreach ($list as $item) {

                    //TODO 消息体
                    $config = \Swoft::getBean('config');
                    $sys_msg = $config->get('sysMsg');
                    $data = array();
                    $extra = $sys_msg;
                    $extra['isRich'] = 1;
                    $extra['imgUrl'] = $send_cover;
                    $extra['title'] = $extra['msgTitle'] = "供应商报告";
                    $extra['commendUser'] = array();
                    $extra['data'] = [];
                    $extra['showData'] = [];
                    $extra['Url'] = $url . '?sds_id=' . $item['sdsId'];
                    $extra["msgContent"] = $extra["content"] = "点击查看您上周报告";
                    $data['extra'] = $extra;

                    //TODO 发送
                    sendInstantMessaging('1', (string)$item['userId'], json_encode($data['extra']));

                    $send_user_id[] = $item['userId'];
                }

            if (!empty($send_user_id)){
                //修改已发送状态
                $this->userData->updateSupplierData($send_user_id);
            }

            if (!empty($no_send_user_id)){
                //发送不成功修改
                $this->userData->updateStatusSupplierData($no_send_user_id);
            }

            //发送记录
            $str = "总报告：{$count}份，已发送:".count($send_user_id)."份,已发送用户：";

            if (!empty($send_user_id)) $str.= implode(',',$send_user_id);
            return [];
        }
    }

    /**
     * 刷新自动报价产品缓存
     * @param Request $request
     * @return array
     */
    public function refresh_auto_offer_product(Request $request)
    {
        $pro_ids = $request->post('pro_id');
        if(empty($pro_ids)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $keys = '@OfferProduct_';
            $pro_cache_key = '@OfferProName_';
            $pro_arr = json_decode($pro_ids,true);
            if(!empty($pro_arr)){
                /* @var ElasticsearchLogic $elastic_logic */
                foreach ($pro_arr as $key => $tmp) {
                    $pro_id = $tmp['pro_id'];
                    $user_id = $tmp['user_id'];
                    if($this->redis->exists($pro_cache_key . $pro_id)){
                        //删除缓存数据
                        $tokenize_cache = $this->redis->get($pro_cache_key . $pro_id);
                        if(!empty($tokenize_cache)){
                            $tokenize_list = json_decode($tokenize_cache,true);
                            foreach ($tokenize_list as $item) {
                                if($this->redis->exists($keys . md5($item))){
                                    $this->redis->sRem($keys . md5($item),$pro_id . '#' . $user_id);
                                }
                            }
                            $this->redis->delete($pro_cache_key .$pro_id);
                        }
                    }

                    $proInfo = $this->proData->getProductInfo($pro_id);
                    if(!empty($proInfo)){
                        $cache_list = [];
                        $elastic_logic = App::getBean(ElasticsearchLogic::class);
                        $tag_list_analyzer = $elastic_logic->offerProAnalyzer($pro_id);
                        if(isset($tag_list_analyzer) && !empty($tag_list_analyzer)){
                            foreach ($tag_list_analyzer as $analyzer) {
                                $token_key = $keys . md5($analyzer);
                                $this->redis->sAdd($token_key, $pro_id . '#' . $user_id);
                                $cache_list[] = $analyzer;
                            }
                        }
                        if(!empty($cache_list)){
                            $this->redis->set($pro_cache_key . $pro_id,json_encode($cache_list));
                        }
                    }
                }
            }
            $code = 1;
            $result = [];
            $msg = '缓存成功';
        }
        return compact("code","result","msg");
    }


    /**
     * @return array
     * @throws DbException
     */
    public function historicalBuy()
    {
        Log::info('历史发布采购商微信激活开启');
        $start_time = strtotime('2019-07-01');
        $end_time = strtotime(date('Y-m-d H:i',strtotime('-15 day')));
        $params = [
            ['add_time','between',$start_time,$end_time],
        ];
        $buy_list = $this->buyData->getLastBuyIds($params);
        if(!empty($buy_list)){
            Log::info("提醒采购id列表:" . json_encode($buy_list));
            $search_params = [
                ['buy_id','IN',$buy_list]
            ];
            $buy_info_list = $this->buyData->getBuyList($search_params,['buy_id','remark','amount','unit','expire_time','user_id','add_time']);
            if(!empty($buy_info_list)){
                $config = \Swoft::getBean('config');
                $msg_temp = $config->get('last_buy_msg');
                $tempId = $msg_temp['temp_id'];
                $grayscale = getenv('IS_GRAYSCALE');
                $test_list = $this->userData->getTesters();
                $send_count = 0;
                $send_user_list = [];
                foreach ($buy_info_list as $item) {
                    if(($grayscale == 1 && !in_array($item['userId'], $test_list))){
                        continue;
                    }
                    //发送数不大于1000人
                    if($send_count >= 1000){
                        break;
                    }
                    //判断是否是最后一条
                    $add_time = $item['addTime'] + 1;
                    $user_buy_list = $this->buyData->getUserByIds($item['userId'],$add_time);
                    if(empty($user_buy_list)){
                        $openId = $this->userData->getUserOpenId($item['userId']);
                        if(!empty($openId)){
                            $tmp_data = $msg_temp['data'];
                            $expire_time = empty($item['expireTime']) ? '' : date('Y年n月j日 H:i:s', $item['expireTime']);
                            Log::info("用户{$item['userId']}发送提醒消息");
                            $tmp_data['keyword1']['value'] = $item['buyId'];
                            $tmp_data['keyword2']['value'] = (string)$item['remark'];
                            $tmp_data['keyword3']['value'] = (string)$item['amount'] . $item['unit'];
                            $tmp_data['keyword4']['value'] = $expire_time;
                            $msg_temp['data'] = $tmp_data;
                            $this->wechatLogic->send_wechat_message($openId, $tempId, $msg_temp,'',1);
                            $send_count += 1;
                            $send_user_list[] = $item['userId'];
                        }
                    }else{
                        Log::info("用户{$item['userId']}有发布最新采购，不发送消息");
                    }
                }
                write_log(2,'15天前发布过采购微信模板消息接收人：' . json_encode($send_user_list));
            }
        }
        Log::info('历史发布采购商微信激活结束');
        return ['历史发布采购商微信提醒'];
    }
}
