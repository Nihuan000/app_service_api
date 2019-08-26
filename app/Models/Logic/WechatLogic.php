<?php
/**
 * This file is part of Swoft.
 */

namespace App\Models\Logic;

use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Log\Log;
use Swoft\Redis\Redis;

/**
 * 微信模板消息发送
 * @Bean()
 * @uses      WechatLogic
 */
class WechatLogic
{
    /**
     * @Inject("appRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @param string $toUser
     * @param string $template_id
     * @param array $msg_body
     * @param string $url
     * @param int $has_small_pro
     */
    public function send_wechat_message(string $toUser, string $template_id, array $msg_body, string $url = '', int $has_small_pro = 0)
    {
        if($this->redis->exists('access_token')){
            $access_token = $this->redis->get('access_token');
        }else{
            $config = \Swoft::getBean('config');
            $wechat_account = $config->get('WechatMsg');
            $wechat_type = $wechat_account['wechat_type'];
            $wechat_appid = $wechat_account['wechat_appid'];
            $wechat_secret = $wechat_account['wechat_secret'];
            $res = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=' . $wechat_type . '&appid='. $wechat_appid.'&secret=' . $wechat_secret);
            $res = json_decode($res, true);
            $access_token = $res['access_token'];
            $this->redis->setex("access_token",50,$access_token);
        }

        if(!empty($access_token)){
            Log::info('发送给:' . $toUser . ',内容:' . $msg_body['keyword2']['value']);
            sendTemplet($access_token,$toUser,$msg_body,$template_id, $url, $has_small_pro);
        }
    }
}
