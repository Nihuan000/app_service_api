<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 2019/11/15
 * Time: 下午3:24
 * Desc: 个推发送处理层
 */

namespace App\Models\Logic;

use Exception;
use Swoft\Bean\Annotation\Bean;

/**
 * 个推消息发送
 * @Bean()
 * @uses      GetuiLogic
 */
class GetuiLogic
{

    /**
     * 发送短信消息
     * @param array $msg_list 消息列表 [['cid' => '', 'content' => []]]
     * @param int $msg_type 1:批量单推(内容不同) 2:批量推(内容相同)
     * @return bool
     * @throws Exception
     */
    public function send_push_message(array $msg_list,$msg_type = 1)
    {
        $task_id = 'GET' . create_guid();
        $content = json_encode($msg_list,JSON_UNESCAPED_UNICODE);
        $send_record = "Taskid:{$task_id}\n内容:{$content}". PHP_EOL;
        write_log(5,$send_record);
        return getui_message($task_id,$msg_list);
    }

    /**
     * 通用短信模板内容组合
     * @param string $mark 模板标识
     * @param string $cid 用户唯一设备id
     * @param string $title 标题
     * @param string $msg 通知内容
     * @param string $pic 图片内容
     * @param array $extra ，扩展字段,目前可选['url','type','id']
     * @return array
     */
    public function template_combination($mark, $cid = '', $title = '',$msg = '', $pic = '', $extra = [])
    {
        $getui_msg = [];
        //系统消息
        $config = \Swoft::getBean('config');
        $template_group = $config->get('getuiMsgTemplate');
        if(isset($template_group[$mark])) {
            $content = $template_group[$mark];
            ### 自定义信息判断开始 ###
            //标题
            if(!empty($title)){
                $content['title'] = $title;
            }

            //内容
            if(!empty($msg)){
                $content['content'] = $msg;
            }

            //图片
            if(!empty($pic)){
                $content['pic'] = $pic;
            }

            //跳转id
            if(isset($extra['id'])){
                $content['id'] = (int)$extra['id'];
            }

            //跳转类型
            if(isset($extra['type'])){
                $content['type'] = (int)$extra['type'];
            }

            //跳转地址
            if(isset($extra['url'])){
                $content['url'] = (int)$extra['url'];
            }
            ### 自定义信息判断结束 ###
            if(!empty($cid)){
                //(单个|批量)单推模板，个性化消息内容
                $getui_msg = ['cid' => $cid, 'content' => $content];
            }else{
                //批量推模板,多用户内容相同
                $getui_msg = $content;
            }
        }
        return $getui_msg;
    }
}
