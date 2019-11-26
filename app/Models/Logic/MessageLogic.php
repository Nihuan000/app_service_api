<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 2019/11/15
 * Time: 下午3:24
 * Desc: 系统消息发送处理层
 */

namespace App\Models\Logic;

use Swoft\Bean\Annotation\Bean;

/**
 * 系统消息发送
 * @Bean()
 * @uses      MessageLogic
 */
class MessageLogic
{

    /**
     * 发送系统消息
     * @param $fromId
     * @param $targetId
     * @param $content
     * @param int $is_batch
     * @return bool
     */
    public function send_system_message($fromId, $targetId,$content,$is_batch = 0)
    {
        $task_id = 'MSG' . create_guid();
        $send_record = '任务id:' . $task_id . "\n内容:{$content['msgContent']}\n 接收者：" . json_encode($targetId) . PHP_EOL;
        if(empty($content['msgContent'])){
            write_log(1,$send_record . '空消息体，发送失败' . PHP_EOL);
            return false;
        }
        write_log(1,$send_record);
        return sendInstantMessaging($fromId,$targetId,json_encode($content),$is_batch, $task_id);
    }

    /**
     * 通用消息模板内容组合
     * @param string $mark 模板标识
     * @param array $data 变量关联数组 [['key1','key2'],['value1','value2']]
     * @param array $extra 扩展字段，目前可选['url','id']
     * @return array
     */
    public function template_combination($mark = '',$data = [],$extra = [])
    {
        $notice = [];
        //系统消息
        $config = \Swoft::getBean('config');
        $sys_msg = $config->get('sysMsg');
        $template_group = $config->get('sysMsgTemplate');
        if(isset($template_group[$mark])){
            $template = $template_group[$mark];
            $content = $template['content'];
            //是否包含变量,替换模板变量
            if(isset($template['has_params']) && $template['has_params'] == 1){
                if(!empty($data)){
                    $content = str_replace($data[0],$data[1],$template['content']);
                }
            }

            ################## msgContent内容替换 ###################
            $keyword = str_replace('#','',$template['keyword']);
            $msg_content = str_replace($template['keyword'],$keyword,$content);
            //发送系统消息
            ################## 消息基本信息开始 #######################
            $sys_msg['title'] = $sys_msg['msgTitle'] = $template['title'];
            $sys_msg['msgContent'] = $msg_content;
            ################## 消息基本信息结束 #######################

            ################## 消息扩展字段开始 #######################
            $extraData = $extra;
            $extraData['keyword'] = $template['keyword'];
            $extraData['type'] = $template['type'];
            ################## 消息扩展字段结束 #######################

            $sys_msg['data'] = [$extraData];
            $sys_msg['content'] = $content;
            $notice = $sys_msg;
        }

        return $notice;
    }
}
