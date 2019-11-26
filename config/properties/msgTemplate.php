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

    /**
     * 短信类模板
     * label: 签名
     * content: 短信内容
     * short_url: 短连接
     * has_params: 内容是否包含变量
     */
    'activateSms' => [
        'supplier_recall' => [
            'lable' => '【搜布】',
            'content' => '刚刚{$var}浏览了您的店铺，赶快打开搜布更新下产品吧！',
            'short_url' => 'http://t.cn/AiE6eHns',
            'has_params' => 1
        ],
        //90天未登录采购商新版本提示
        'inactive_buyer' => [
            'label' => '【搜布】',
            'content' => "小布近期发布了新版本，快来体验一下吧！ ",
            'short_url' => 'http://t.cn/AiRXq5ew',
            'has_params' => 0
        ],
        //供应商15天未登录
        'supplier_no_login_15th' => [
            'label' => '【搜布】',
            'content' => "又有新的采购信息在等您报价了，快来看看吧！戳 ",
            'short_url' => 'http://t.cn/AiEIcIy4',
            'has_params' => 0
        ],
        //采购商3天未登录
        'buyer_no_login_7th' => [
            'label' => '【搜布】',
            'content' => '您不在的时间里，共有{$var}+款{$var}面料发布,立即查看。',
            'short_url' => 'http://t.cn/Ai9QVgpa',
            'has_params' => 1
        ],
    ],

    /**
     * 系统消息模板
     * content: 消息内容
     * title: 消息标题
     * keyword:点击关键词
     * type:消息跳转类型
     * has_params: 内容是否包含变量
     */
    'sysMsgTemplate' => [

        //供应商7日未登录,付费用户
        'pay_supplier_no_login_7th' => [
            'content' => '刚刚>X<浏览了您的搜布店铺，#赶快去报价吧#！',
            'title' => '有人浏览了您的搜布店铺',
            'keyword' => '#赶快去报价吧#',
            'type' => 11,
            'has_params' => 1
        ],

        //供应商7日未登录,免费用户
        'free_supplier_no_login_7th' => [
            'content' => '刚刚>X<浏览了您的搜布店铺，#赶快去看看吧#！',
            'title' => '有人浏览了您的搜布店铺',
            'keyword' => '#赶快去看看吧#',
            'type' => 11,
            'has_params' => 1
        ],

        //供应商15天未登录，激活消息
        'supplier_no_login_15th' => [
            'content' => '又有新的采购信息在等您报价了，快来看看吧，#点击查看采购信息#',
            'title' => '',
            'keyword' => '#点击查看采购信息#',
            'type' => 11,
            'has_params' => 0
        ],

        //报价排行榜消息
        'offer_ranking_msg' => [
            'content' => '恭喜您目前处于报价排行榜第>X<名，#点击查看榜单#',
            'title' => '报价排行榜',
            'keyword' => '#点击查看榜单#',
            'type' => 18,
            'has_params' => 1
        ],

        //采购商3日未登录
        'buyer_no_login_3th' => [
            'content' => '您不在的时间里，共有>X<+款>Y<面料发布, #立即查看#',
            'title' => '',
            'keyword' => '#立即查看#',
            'type' => 18,
            'url' => '',
            'has_params' => 1
        ],
    ],

    /**
     * 个推消息模板
     * content: 消息内容
     * title: 消息标题
     * keyword:点击关键词
     * type:消息跳转类型
     * has_params: 内容是否包含变量
     */
    'getuiMsgTemplate' => [
        //供应商7日未登录
        'supplier_no_login_7th' => [
            'content' => '刚刚>X<浏览了您的搜布店铺，赶快去报价吧！',
            'title' => '有人浏览了您的搜布店铺',
            'pic' => '',
            'type' => 11,
            'id' => 0,
            'url' => ''
        ],
        //采购商3日未登录
        'buyer_no_login_3th' => [
            'content' => '您不在的时间里，共有>X<+款>Y<面料发布',
            'title' => '您关注的店铺上新啦',
            'pic' => '',
            'type' => 5,
            'id' => 0,
            'url' => ''
        ],
    ],
];
