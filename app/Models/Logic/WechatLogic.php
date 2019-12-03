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
        if($this->redis->has('access_token')){
            $access_token = $this->redis->get('access_token');
        }else{
            $config = \Swoft::getBean('config');
            $wechat_account = $config->get('WechatMsg');
            $wechat_type = $wechat_account['wechat_type'];
            $wechat_appid = $wechat_account['wechat_appid'];
            $wechat_secret = $wechat_account['wechat_secret'];

            $ud = curl_init();
            curl_setopt($ud,CURLOPT_URL,'https://api.weixin.qq.com/cgi-bin/token?grant_type=' . $wechat_type . '&appid='. $wechat_appid.'&secret=' . $wechat_secret);
            curl_setopt($ud, CURLOPT_POST, 1);
            curl_setopt($ud, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ud, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ud, CURLOPT_HTTPHEADER, ["Accept-Charset: utf-8"]);
            curl_setopt($ud, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ud, CURLOPT_AUTOREFERER, 1);
            curl_setopt($ud, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ud);
            curl_close($ud);
            $res = json_decode($res, true);
            $access_token = $res['access_token'];
            $this->redis->set("access_token",$access_token,50);
        }

        if(!empty($access_token)){
            write_log(6,'发送给:' . $toUser . ',内容:' . json_encode($msg_body['data'],JSON_UNESCAPED_UNICODE));
            sendTemplet($access_token,$toUser,$msg_body,$template_id, $url, $has_small_pro);
        }
    }
}
