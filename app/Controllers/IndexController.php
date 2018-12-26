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
     */
    public function offer_test(Request $request)
    {
        $msg_arr = explode('#',$request->post('msg_record'));
        $user_id = (int)$msg_arr[0];
        $buy_id = (int)$msg_arr[1];
        $buyInfo = $this->buyData->getBuyInfo($buy_id);
        $buyer = $this->userData->getUserInfo((int)$buyInfo['userId']);
        $config = \Swoft::getBean('config');
        $sys_msg = $config->get('offerMsg');

        //发送系统消息
        ################## 消息展示内容开始 #######################
        $extra = $sys_msg;
        $extra['image'] = !is_null($buyInfo['pic']) ? get_img_url($buyInfo['pic']) : '';
        $extra['type'] = 1;
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
        $name = '未知结果';
        if($sendRes){
            $name = '发送成功';
        }
        return compact('name');
    }
}
