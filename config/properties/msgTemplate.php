<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-24
 * Time: 上午11:42
 * Desc: 消息模板配置
 */

return [
    //报价助手
    'offerMsg' => array(
        'msgTitle' => '',
        'msgContent' => '',
        'imgUrl' => '',
        'type' => 1,
        'id' => '0',
        'buy_id' => '0',
        'offer_id' => '0',
        'image' => '',
        'name' => '',
        'status' => '0',
        'bigPrice' => '',
        'units' => '',
        'cutPrice' => '',
        'cut_units' => '',
        'amount' => '0',
        'unit' => '',
        'title' => ''
    ),

    //系统消息
    'sysMsg' => array(
        'msgTitle' => '',
        'msgContent' => '',
        'imgUrl' => '',
        'title' => '',
        'content' => '',
        'data' => [],
        'showData' => [],
        'commendUser' => []
    ),

    'offerSms' => [
        //邀请报价
        'invitate_offer' => '【搜布】收到邀请。买家>NAME<发布了面料采购，邀请您为他报价。立即前往 http://t.cn/Ai9QMlv1',
    ],

    //激活短信
    'activateSms' => [
        'supplier_recall' => '【搜布】刚刚有人浏览了您的店铺，赶快打开搜布更新下产品吧！ ',
        'inactive_buyer' => '【搜布】小布近期发布了新版本，快来体验一下吧！ http://t.cn/AiRXq5ew', //90天未登录采购商新版本提示
    ],
];
