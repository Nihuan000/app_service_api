<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-24
 * Time: 上午11:08
 */


/**
 * 拼接图片地址
 * @author Nihuan
 * @param $pic
 * @return string
 */
function get_img_url($pic)
{
    $oss_path = getenv('OSS_ONLINE_URL');
    if( substr($pic,0,1) == '/'){
        $pic = $oss_path . $pic;
    }
    return $pic;
}


/**
 * 行业短信
 * @author Nihuan
 * @param string $phone
 * @param string $content
 * @param int $msg_type 1:验证码 2:消息
 * @param int $send_route 1:行业短信 2:营销短信
 * @return bool
 */
 function sendSms(string $phone, string $content, int $msg_type=1, int $send_route = 1)
{
    $config = \Swoft::getBean('config');
    $filter_phone = $config['filter_phone'];
    $industry_config = $config['IndustrySms'];
    $marketing_config = $config['MarketingSms'];
    $sms_switch = $config['SMS_SWITCH'];
    $is_service = false;
    if(in_array($phone,$filter_phone) && $msg_type == 2){
        $is_service = true;
    }

    if($send_route == 1){
        $account = $industry_config['I_account'];
        $password = $industry_config['I_password'];
    }else{
        $account = $marketing_config['M_account'];
        $password = $marketing_config['M_password'];
    }

    if($is_service == false && $sms_switch == 1){
        $sendSms = ['phone'=>$phone, 'msg'=>urlencode($content),'account' => $account,'password' => $password,'report' => true];
        $postFields = json_encode($sendSms);
        $ch = curl_init ();
        curl_setopt( $ch, CURLOPT_URL, "http://vsms.253.com/msg/send/json" );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'
            )
        );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt( $ch, CURLOPT_TIMEOUT,1);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec ( $ch );
        if (false == $ret) {
            $result =  false;
        } else {
            $rsp = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result =  false;
            } else {
                $result = true ;
            }
        }
        curl_close ( $ch );
        return $result;
    }else{
        return true;
    }
}


/**
 * 腾讯云通讯消息发送器
 * @author Nihuan
 * @param $fromId
 * @param $uid
 * @param $content
 * @return bool
 */
 function sendInstantMessaging($fromId,$uid,$content)
{
    $content_arr = json_decode($content,true);
    $offline_template = custom_offline($fromId,$content_arr);
    $offline_apns = apns_info($fromId);
    $android_info = android_info();
    $params = [
        'SyncOtherMachine' => 2,
        'MsgRandom' => rand(1, 65535),
        'MsgTimeStamp' => time(),
        'From_Account'=> (string)$fromId,
        'To_Account' => (string)$uid,
        'MsgBody' => [['MsgType'=>'TIMCustomElem','MsgContent'=> ['Data' => $content , 'Desc' => is_null($content_arr['msgContent']) ? '' : $content_arr['msgContent']]]],
        'OfflinePushInfo' => ['PushFlag' => 0, 'Ext' => is_null($offline_template) ? '' : $offline_template , 'ApnsInfo'=> $offline_apns ,'AndroidInfo'=> $android_info ]
    ];
    $paramsString = json_encode($params,JSON_UNESCAPED_UNICODE);
    $parameter = IMService();

    $curl_params = ['url'=>'https://console.tim.qq.com/v4/openim/sendmsg?' . $parameter, 'timeout'=>15];
    $curl_params['post_params'] = $paramsString;
    $curl_result = CURL($curl_params, 'post');

    $reStatus = json_decode($curl_result);
    if($reStatus->ErrorCode == 0) {
        return true;
    }
    else {
        return false;
    }
}


/**
 * 公共curl方法
 * @param $params
 * @param string $request_type
 * @return mixed
 */
 function CURL($params, $request_type = 'get') {
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $params['url']);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $params['timeout']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    if( isset($params['other_options']) ) {
        curl_setopt_array($ch, $params['other_options']);
    }

    if($request_type === 'post') {
        curl_setopt($ch, CURLOPT_POST, TRUE);
        if (isset($params['post_params'])) curl_setopt($ch,CURLOPT_POSTFIELDS,$params['post_params']);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * 离线消息字段处理
 * @author Nihuan
 * @param $type
 * @param $content
 * @return string
 */
function custom_offline($type,$content){
    $data = [];
    $data['id'] = $type;
    switch($type){
        case 1:
            $data['type'] = 1;
            break;

        case 2:
            $data['offer_id'] = $content['offer_id'];
            $data['buy_id'] = $content['id'];
            $data['type'] = $content['type'];
            break;

        case 6:
            $data['type'] = $content['type'];
            $data['message_type'] = $content['message_type'];
            $data['order_num'] = $content['order_num'];
            $data['pre_id'] = $content['pre_id'];
            $data['order_sub_num'] = $content['order_sub_num'];
            break;

        case 7:
            $data['sco_id'] = $content['sco_id'];
            $data['user_type'] = $content['user_type'];
            $data['type'] = $content['type'];
            break;
        case 8:
            $data['id'] = "8";
            $data['content'] = $content['user_type'];
            $data['type'] = $content['content'];
            break;
    }

    return json_encode($data);
}

/**
 * 离线消息多媒体/ios
 * @author Nihuan
 * @param $from_id
 * @return mixed
 */
function apns_info($from_id){
    $data['Title'] = '';
    switch ($from_id){
        case 1: $data['Title'] = "系统消息";break;
        case 2: $data['Title'] = "报价通知";break;
        case 6: $data['Title'] = "订单助手";break;
        case 7: $data['Title'] = "评价通知";break;
        case 8: $data['Title'] = "搜布活动";break;
    }
    $data['Sound'] = "push.aiff";
    $data['SubTitle'] = "";
    $data['Image'] = "https://image.isoubu.com/sysMsg/5a439a9d9af73.png";
    return $data;
}

/**
 * 离线消息多媒体/android
 * @Author Nihuan
 * @return array
 */
function android_info(){
    $data['Sound'] = "push.mp3";
    return $data;
}

/**
 * 腾讯云通讯参数生成
 * @Author Nihuan
 * @Version 1.0
 * @Date 16-11-03
 * @return string
 */
function IMService(){
    $config = get_im_config();
    $user = $config['IM_USER'];
    $usersig = getIMtoken($user);
    $sdkappid = $config['IM_APPID'];
    $content_type = $config['IM_CONTENT_TYPE'];
    $parameter =  "usersig=" . $usersig
        . "&identifier=" . $user
        . "&sdkappid=" . $sdkappid
        . "&contenttype=" . $content_type;
    return $parameter;
}

/**
 * 导入新用户到腾讯云通讯
 * @Author Nihuan
 * @Version 1.0
 * @Date 16-11-03
 * @param $uid
 * @return bool
 */
function genIMKey($uid){
    $parameter = IMService();
    $params = [
        'Identifier' => (string)$uid
    ];
    $paramsString = json_encode($params,JSON_UNESCAPED_UNICODE);
    $curl_params = ['url'=>'https://console.tim.qq.com/v4/im_open_login_svc/account_import?' . $parameter, 'timeout'=>15];
    $curl_params['post_params'] = $paramsString;
    $curl_result = CURL($curl_params, 'post');

    $reStatus = json_decode($curl_result);
    if($reStatus->ErrorCode == 0) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * 云通讯在线状态获取
 * @Author Nihuan
 * @Version 1.0
 * @Date 16-12-12
 * @param $uid
 * @return bool|string
 */
function getIMstate($uid){
    $State = 'Online';
    $parameter = IMService();
    $params = [
        'To_Account' => [(string)$uid]
    ];
    $paramsString = json_encode($params, JSON_UNESCAPED_UNICODE);
    $curl_params = ['url' => 'https://console.tim.qq.com/v4/openim/querystate?' . $parameter, 'timeout' => 15];
    $curl_params['post_params'] = $paramsString;
    $curl_result = CURL($curl_params, 'post');

    $reStatus = json_decode($curl_result);
    if ($reStatus->ErrorCode == 0) {
        $result = current($reStatus->QueryResult);
        if (!is_null($result->State)) {
            $State = $result->State;
        }
    } else {
        return false;
    }
    return $State;
}

/**
 * 腾讯云通讯token生成
 * @Author Nihuan
 * @Version 1.0
 * @Date 16-10-28
 * @param $uid
 * @return bool
 */
function getIMtoken($uid){
    $config = get_im_config();
    $token = '';
    if($uid == false){
        return false;
    }else{
        $private_key = LOCAL_EXECUTION_PATH . 'IMcloud/private_key';
        $bin_path = LOCAL_EXECUTION_PATH . 'IMcloud/bin/signature';
        $log = LOCAL_EXECUTION_PATH . 'im_log/im_log_' . date('y-m') . '.log';
        $appid = $config['IM_APPID'];
        $command = $bin_path
            . ' ' . escapeshellarg($private_key)
            . ' ' . escapeshellarg($appid)
            . ' ' . escapeshellarg($uid);
        exec($command, $out, $status);
        if ($status != 0)
        {
            $info = date('Y-m-d H:i:s') . ', user_id: ' . $uid . ', msg:' . json_encode($out);
            file_put_contents($log,$info);
            return null;
        }
        if(!empty($out)){
            $token = $out[0];
        }
    }
    return $token;
}


/**
 * 获取腾讯云配置
 * @author Nihuan
 * @return mixed
 */
function get_im_config()
{
    $config = \Swoft::getBean('config');
    $InstantMSg_config = $config['InstantMSg'];
    return $InstantMSg_config;
}


/**
 * 个推消息发送
 * @author Nihuan
 * @param $cid
 * @param $content
 * @return array
 * @throws Exception
 */
function get_message($cid,$content)
{
    $config = \Swoft::getBean('config');
    $account = $config['getuiMsg'];
    $category = json_encode($content);

    //模板设置
    $template = new IGtTransmissionTemplate();
    $template->set_appId($account['GETUI_APP_ID']);
    $template->set_appkey($account['GETUI_APP_KEY']);
    $template->set_transmissionType(2);
    $template->set_transmissionContent($category);

    //透传内容设置
    $payload = new IGtAPNPayload();
    $payload->contentAvailable = 0;
    $payload->category = $category;
    $payload->badge = "+1";

    //消息体设置
    $alterMsg = new DictionaryAlertMsg();
    $alterMsg->body = (string)$content['content'];
    $alterMsg->title = $content['title'];
    $payload->alertMsg = $alterMsg;
    if($content['pic'] != ''){
        $media = new IGtMultiMedia();
        $medicType = new MediaType();
        $media->type = $medicType::pic;
        $media->url = $content['pic'];
        $payload->add_multiMedia($media);
    }

    $template->set_apnInfo($payload);

    //推送实例化
    $igt = new IGeTui(NULL,$account['GETUI_APP_KEY'],$account['GETUI_MASTER_SECRET'],false);

    //个推信息体
    $messageNoti = new IGtSingleMessage();
    $messageNoti->set_isOffline(true);//是否离线
    $messageNoti->set_offlineExpireTime(24 * 60 * 60);//离线时间
    $messageNoti->set_data($template);//设置推送消息类型

    //接收方
    $target = new IGtTarget();
    $target->set_appId($account['GETUI_APP_ID']);
    $target->set_clientId($cid);

    try {
        $rep = $igt->pushMessageToSingle($messageNoti, $target);
    }catch(RequestException $e){
        $requstId =$e->getRequestId();
        $rep = $igt->pushMessageToSingle($messageNoti, $target,$requstId);
    }
    return $rep;
}

/**
 * 执行记录id
 * @param $type
 * @param $data
 */
function write_log($type,$data)
{
    $log_dir = '/srv/soubuSoa/runtime/uploadfiles/';
    $file = '';
    switch ($type){
        case 1:
            $file = 'norefresh_' . date('Y_m_d') . '.log';
            break;

        case 2:
            $file = 'notice_' . date('Y_m_d') . '.log';
            break;
    }
    if(!empty($file)){
        file_put_contents($log_dir . $file,$data . PHP_EOL,FILE_APPEND);
    }
}

/**
 * 随机数
 * @return string
 */
function create_guid(){
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $uuid = substr($charid, 0, 8).substr($charid, 8, 4)
        .substr($charid,12, 3);
    return $uuid;
}

/**
 * 近似值匹配
 * @param $judgment
 * @param $match_list
 * @return mixed
 */
function similar_acquisition($judgment,$match_list)
{
    $current_level = [];
    foreach ($match_list as $ck => $cv) {
        if($ck >= $judgment){
            $current_level[] = $ck;
        }
    }
    krsort($current_level);
    $current_score = current($match_list);
    $current_match_value = $match_list[$current_score];
    return $current_match_value;
}