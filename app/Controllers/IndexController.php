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
use App\Models\Data\UserData;
use App\Models\Logic\UserLogic;
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
     * @throws \Swoft\Db\Exception\DbException
     */
    public function supplier_test(Request $request)
    {
        $user_id = $request->post('user_id');
        if(empty($user_id)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $last_days = date('Y-m-d',strtotime("-50 day"));
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
}
