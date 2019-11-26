<?php
/**
 * 消息发送集成类
 * 包含: 个推，系统消息，短信，微信模板消息
 */

namespace App\Controllers;

use Swoft\Http\Message\Bean\Annotation\Middleware;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Server\Bean\Annotation\RequestMethod;

/**
 * Class MessageController
 * @Middleware(class=ActionVerifyMiddleware::class)
 * @Controller(prefix="/message")
 * @package App\Controllers
 */
class MessageController{

    /**
     * 个推消息接收接口
     * @RequestMapping(route="/getui", method=RequestMethod::POST)
     * @param Request $request
     * @return array
     */
    public function getui(Request $request): array
    {

    }

    /**
     * 短信消息接收接口
     * @RequestMapping(route="/sendSms", method=RequestMethod::POST)
     * @param Request $request
     * @return array
     */
    public function sendSms(Request $request): array
    {

    }

    /**
     * 系统消息接收接口
     * @RequestMapping(route="/sendNotice", method=RequestMethod::POST)
     * @param Request $request
     * @return array
     */
    public function sendNotice(Request $request): array
    {

    }

    /**
     * 模板消息(微信)接收接口
     * @RequestMapping(route="/templateMessage", method=RequestMethod::POST)
     * @param Request $request
     * @return array
     */
    public function templateMessage(Request $request): array
    {

    }
}
