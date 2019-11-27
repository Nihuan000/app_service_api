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
 * 短信通知(批量发多个号码格式[phone1,phone2,phone3])
 * @param string $phone
 * @param string $content
 * @param int $msg_type 1:验证码|行业短信 2:营销短信
 * @param string $params 扩展参数，变量短信时传参
 * @return bool
 * @author Nihuan
 */
 function sendSms(string $phone, string $content, int $msg_type=1, string $params = '')
{
    $config = \Swoft::getBean('config');
    $filter_phone = $config['filter_phone'];
    $industry_config = $config['IndustrySms'];
    $marketing_config = $config['MarketingSms'];
    $sms_switch = env('SMS_SWITCH');
    $is_service = false;
    if(in_array($phone,$filter_phone) && $msg_type == 2){
        $is_service = true;
    }

    $shuffix = '';
    if($msg_type == 1){
        $account = $industry_config['I_account'];
        $password = $industry_config['I_password'];
    }else{
        $account = $marketing_config['M_account'];
        $password = $marketing_config['M_password'];
        $shuffix = ' 退订回T';
    }
    if($is_service == false && $sms_switch == 1){
        $content .= $shuffix;
        $sendSms = ['phone'=>$phone, 'msg'=>urlencode($content),'account' => $account,'password' => $password,'report' => true];
        $url = "http://vsms.253.com/msg/send/json";
        if(!empty($params)){
            unset($sendSms['phone']);
            $sendSms['params'] = $params;
            $sendSms['msg'] = $content;
            $url = "http://vsms.253.com/msg/variable/json";
        }
        $postFields = json_encode($sendSms);
        $ch = curl_init ();
        curl_setopt( $ch, CURLOPT_URL, $url);
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
        $record_data = $sendSms;
        $record_data['msg'] = urldecode($record_data['msg']);
        write_log(3,json_encode($record_data,JSON_UNESCAPED_UNICODE) . '->' . $ret);
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
        write_log(3,'测试数据:' . $phone . ',内容:' . $content);
        return true;
    }
}


/**
 * 腾讯云通讯消息发送器
 * @param $fromId
 * @param $uid
 * @param $content
 * @param int $is_batch
 * @param string $task_id 任务id
 * @return bool
 * @author Nihuan
 */
 function sendInstantMessaging($fromId,$uid,$content,$is_batch = 0, $task_id = '')
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
        'To_Account' => $is_batch == 0 ? (string)$uid : (array)$uid,
        'MsgBody' => [['MsgType'=>'TIMCustomElem','MsgContent'=> ['Data' => $content , 'Desc' => is_null($content_arr['msgContent']) ? '' : $content_arr['msgContent']]]],
        'OfflinePushInfo' => ['PushFlag' => 0, 'Ext' => is_null($offline_template) ? '' : $offline_template , 'ApnsInfo'=> $offline_apns ,'AndroidInfo'=> $android_info ]
    ];
    $paramsString = json_encode($params,JSON_UNESCAPED_UNICODE);
    $parameter = IMService();

    if($is_batch == 1){
        $url = 'https://console.tim.qq.com/v4/openim/batchsendmsg?';
    }else{
        $url = 'https://console.tim.qq.com/v4/openim/sendmsg?';
    }
    $curl_params = ['url'=> $url . $parameter, 'timeout'=>15];
    $curl_params['post_params'] = $paramsString;
    $curl_result = CURL($curl_params, 'post');

    $reStatus = json_decode($curl_result);
    write_log(1,"taskId:{$task_id}->" . $paramsString . '->' . $curl_result);
    if($reStatus->ErrorCode == 0) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * C2C聊天消息发送
 * @param $fromId
 * @param $uid
 * @param $content
 * @param int $is_batch
 * @param int $SyncOtherMachine 消息是否同步至发送方 1:同步 2:不同步
 * @return bool
 */
function sendC2CMessaging($fromId,$uid,$content,$is_batch = 0, $SyncOtherMachine = 2)
{
    $offline_apns = apns_info($fromId);
    $android_info = android_info();
    $params = [
        'SyncOtherMachine' => $SyncOtherMachine,
        'MsgRandom' => rand(1, 65535),
        'MsgTimeStamp' => time(),
        'From_Account'=> (string)$fromId,
        'To_Account' => $is_batch == 0 ? (string)$uid : (array)$uid,
        'MsgBody' => [['MsgType'=>'TIMTextElem','MsgContent'=> ['Text' => $content]]],
        'OfflinePushInfo' => ['PushFlag' => 0, 'Ext' => is_null($content) ? '' : $content , 'ApnsInfo'=> $offline_apns ,'AndroidInfo'=> $android_info ]
    ];
    $paramsString = json_encode($params,JSON_UNESCAPED_UNICODE);
    $parameter = IMService();

    if($is_batch == 1){
        $url = 'https://console.tim.qq.com/v4/openim/batchsendmsg?';
    }else{
        $url = 'https://console.tim.qq.com/v4/openim/sendmsg?';
    }
    $curl_params = ['url'=> $url . $parameter, 'timeout'=>15];
    $curl_params['post_params'] = $paramsString;
    $curl_result = CURL($curl_params, 'post');

    $reStatus = json_decode($curl_result);
    write_log(3,$paramsString . '->' . $curl_result);
    if($reStatus->ErrorCode == 0) {
        return true;
    }
    else {
        return false;
    }
}


/**
 * 微信模板消息发送
 * @param string $access_token 微信token
 * @param string $toUser 接收人openid
 * @param array $info 消息主体
 * @param string $template_id 模板id
 * @param string $url 跳转地址
 * @param int $has_small_pro 是否跳转小程序
 * @return bool|mixed
 */
function sendTemplet($access_token, $toUser,$info,$template_id,$url = '', $has_small_pro = 0) {
    if(empty($toUser))return false;
    $data['touser'] = $toUser;
    $data['template_id'] = $template_id;
    $data['url'] = is_null($url) ? '' : $info['url'];
    $info['data']['remark']['value'] = "\n" . $info['data']['remark']['value'];
    unset($info['url']);
    unset($info['temp_id']);
    $data['data'] = $info['data'];
    if($has_small_pro == 1){
        $data['miniprogram'] = $info['miniprogram'];
    }

    $ud = curl_init();
    curl_setopt($ud,CURLOPT_URL,"https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token);
    curl_setopt($ud, CURLOPT_CUSTOMREQUEST, strtoupper('POST'));
    curl_setopt($ud, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ud, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ud, CURLOPT_HTTPHEADER, ["Accept-Charset: utf-8"]);
    curl_setopt($ud, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ud, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ud, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ud, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ud, CURLOPT_RETURNTRANSFER, true);
    $tmp = curl_exec($ud);
    curl_close($ud);
    write_log(3,json_encode($data) . '->' . $tmp);
    return json_decode($tmp,true);
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

    if(isset($params['headers'])){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $params['headers']);
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
 * 批量设置个推接收方
 * @param $cid_list
 * @return array
 */
function set_getui_target($cid_list)
{
    $target_list = [];
    $list_count = count($cid_list);
    if($list_count > 1000){
        return $target_list;
    }
    $config = \Swoft::getBean('config');
    $account = $config['GetuiMsg'];
    if(is_array($cid_list)){
        foreach ($cid_list as $item) {
            //接收方1
            $target = new IGtTarget();
            $target->set_appId($account['GETUI_APP_ID']);
            $target->set_clientId($item);
            $target_list[] = $target;
        }
    }

    return $target_list;
}


/**
 * 个推消息批量发送
 * @param string $task_id 任务id
 * @param array $batch_list
 * @return array
 * @throws Exception
 * @author Nihuan
 */
function getui_message($task_id,$batch_list = [])
{
    $config = \Swoft::getBean('config');
    $account = $config['GetuiMsg'];

    require_once env('GETUI_PATH') . '/IGt.Push.php';
    require_once env('GETUI_PATH') . '/igetui/IGt.AppMessage.php';
    require_once env('GETUI_PATH') . '/igetui/IGt.APNPayload.php';
    require_once env('GETUI_PATH') . '/IGt.Batch.php';
    require_once env('GETUI_PATH') . '/igetui/utils/AppConditions.php';
    require_once env('GETUI_PATH') . '/igetui/IGt.MultiMedia.php';

    //推送实例化
    $igt = new IGeTui($account['GETUI_HOST'],$account['GETUI_APP_KEY'],$account['GETUI_MASTER_SECRET'],false);
    $batch = new IGtBatch($account['GETUI_APP_KEY'], $igt);

    foreach ($batch_list as $item) {
        $cid = $item['cid'];
        $content = $item['content'];
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
        if(isset($content['pic']) && $content['pic'] != ''){
            $media = new IGtMultiMedia();
            $medicType = new MediaType();
            $media->type = $medicType::pic;
            $media->url = $content['pic'];
            $payload->add_multiMedia($media);
        }

        $template->set_apnInfo($payload);

        //个推信息体
        $messageNoti = new IGtSingleMessage();
        $messageNoti->set_isOffline(true);//是否离线
        $messageNoti->set_offlineExpireTime(24 * 60 * 60);//离线时间
        $messageNoti->set_data($template);//设置推送消息类型

        //接收方
        $target = new IGtTarget();
        $target->set_appId($account['GETUI_APP_ID']);
        $target->set_clientId($cid);
        $batch->add($messageNoti, $target);
    }

    try {
        $rep = $batch->submit();
        write_log(5,$task_id . '->' . json_encode($rep,JSON_UNESCAPED_SLASHES));
    }catch(RequestException $e){
        write_log(5,$task_id . '_Err: code->' . $e->getCode() . ',Track ->' . $e->getTrace() . ',Message->' . $e->getMessage());
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
            $file = 'message_' . date('Y_m_d') . '.log';
            break;

        case 2:
            $file = 'notice_' . date('Y_m_d') . '.log';
            break;

        case 3:
            $file = 'record_' . date('Y_m_d') . '.log';
            break;

        case 4:
            $file = 'sms_' . date('Y_m_d') . '.log';
            break;

        case 5:
            $file = 'getui_' . date('Y_m_d') . '.log';
            break;

        case 6:
            $file = 'wechat_' . date('Y_m_d') . '.log';
            break;
    }
    if(!empty($file)){
        $date = date('Y-m-d H:i:s');
        $log_date = "【{$date}】 ";
        file_put_contents($log_dir . $file,$log_date . $data . PHP_EOL,FILE_APPEND);
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
 * @param int $get_type 1:取最小 2:取最大
 * @return mixed
 */
function similar_acquisition($judgment,$match_list,$get_type = 1)
{
    $current_level = [];
    $current_match_value = 0;
    foreach ($match_list as $ck => $cv) {
        if($ck >= $judgment){
            $current_level[] = $ck;
        }
    }
    if($get_type == 1){
        ksort($current_level);
    }else{
        krsort($current_level);
    }
    $current_score = current($current_level);
    if(!empty($current_score)){
        $current_match_value = $match_list[$current_score];
    }
    return $current_match_value;
}


/**
 * 唯一码uuid生成
 * @param string $mark
 * @return string
 */
function _uuid_geneal($mark = ''){
    $uuid = '';
    $num_mark = empty($mark) ? '003700' : $mark;
    $tmp_num = microtime(true) * 10000;
    $bit = 31;
    $sec_uuid = $num_mark . $tmp_num;
    $len = strlen($sec_uuid);
    $flx = ceil(($bit - $len) /2);
    for($j=0;$j<$flx;$j++){
        $uuid .= 0;
    }
    $uuid .= $sec_uuid;
    $flx_max = $bit - strlen($uuid);
    for ($i = 0;$i<$flx_max;$i++){
        $uuid .= 0;
    }
    return $uuid;
}

/**
 * 搜索获取接口
 * @Author Nihuan
 * @Version 1.0
 * @Date 16-11-28
 * @param $type
 * @param $params
 * @param $id
 * @param $device
 * @param $from_type
 * @return array|bool
 */
function publicSearch($type, $params, $id, $device = 'activate-task-device', $from_type = 1){
    $es_type = '';
    switch($type){
        case 1:
            $es_type = 'complement';
            break;

        case 2:
            $es_type = 'buy';
            break;

        case 3:
            $es_type = 'product';
            break;

        case 4:
            $es_type = 'shop';
            break;

        case 5:
            $es_type = 'addressBook';
            break;

        case 6:
            //同店推荐,邻家好货,相似推荐
            $es_type = 'listByProduct';
            break;

        case 7:
            //店铺删除
            $es_type = 'shopDelete';
            break;

        case 8:
            //关键词推广产品
            $es_type = 'recommendProduct';
            break;
    }
    $ch = curl_init();
    $request_url = env('SEARCH_HOST');
    $server_name = env('SEARCH_SERVER_NAME');
    $application_name = env('SEARCH_APP_NAME');
    $post_data = [
        'serviceName' => $server_name,
        'applicationName' => $application_name,
        'methodName' => $es_type
    ];
    $header_data = [
        'Content-Type: application/json',
        'deviceId: ' . $device,
        'requestBasic:' . json_encode(['fromType' => (int)$from_type]),
        'userId:' . $id
    ];
    curl_setopt($ch, CURLOPT_URL, $request_url);
    if($params != false)
    {
        if(isset($params)){
            $search_params = $params;
            $post_data['inputParameter'] = $search_params;
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    }
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $curl_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($curl_code == 200){
        if(false === $res){
            return 0;
        }else{
            $result = json_decode($res,true);
            return !empty($result['data']['total']) && $result['data']['total'] >= 0 ? $result['data']['total'] : 0;
        }
    }else{
        return false;
    }
}

/**
 * 短连接生成
 * @param $day
 * @return mixed|string
 */
function get_shot_url($day){
    //新浪短连接不能用了，先给历史有效链接好了
    $short_url = 'http://t.cn/AiE6eHns';
    return $short_url;
}

function resize_oss_img($url)
{

}
