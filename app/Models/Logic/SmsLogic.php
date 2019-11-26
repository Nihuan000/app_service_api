<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 2019/11/15
 * Time: 下午3:24
 * Desc: 短信发送处理层
 */

namespace App\Models\Logic;

use Swoft\Bean\Annotation\Bean;

/**
 * 短信消息发送
 * @Bean()
 * @uses      SmsLogic
 */
class SmsLogic
{

    /**
     * 发送短信消息
     * @param string $phone 手机号(列表)
     * @param string $content 短信内容
     * @param int $msg_type 1:验证码 2:消息
     * @param string $params
     * @return bool
     */
    public function send_sms_message($phone,$content,$msg_type = 2,$params = '')
    {
        $task_id = 'SMS' . create_guid();
        $send_record = "任务id:{$task_id}\n内容:{$content}\n 接收者：" . $phone. PHP_EOL;
        if(!empty($params)){
            $send_record .= "变量内容:{$params}" . PHP_EOL;
        }
        write_log(4,$send_record);
        return sendSms($phone,$content,$msg_type, $params);
    }

    /**
     * 通用短信模板内容组合
     * @param string $mark 短信标识
     * @param array $data 变量关联数组
     *     [['key1','key2'],['value1','value2']]
     * @param array $extra ，目前可选['short_url','lable']
     * @return string
     */
    public function template_combination($mark = '',$data = [], $extra = [])
    {
        $msg = '';
        //短信消息
        $config = \Swoft::getBean('config');
        $sms_group = $config->get('activateSms');
        if(isset($sms_group[$mark])) {
            $template = $sms_group[$mark];
            if(!empty($extra)){
                $template = array_merge($template,$extra);
            }
            if(isset($template['label'])){
                $msg = $template['label'];
            }
            $content = $template['content'];
            //是否包含变量,替换模板变量
            if (isset($template['has_params']) && $template['has_params'] == 1) {
                if (!empty($data)) {
                    $content = str_replace($data[0], $data[1], $template['content']);
                }
            }
            if(!empty($template['short_url'])){
                $content .= $template['short_url'];
            }
            $msg .= $content;
        }

        return $msg;
    }
}
