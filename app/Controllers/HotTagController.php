<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-27
 * Time: 上午11:30
 */

namespace App\Controllers;

use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Redis\Redis;

/**
 * @Controller(prefix="/hotTag")
 */
class HotTagController
{

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;
    private $top_list = [
        1 => '针织面料',
        2 => '棉类面料',
        3 => '麻类面料',
        4 => '呢料毛纺面料',
        5 => '丝绸/真丝面料',
        6 => '化纤面料',
        7 => '蕾丝/绣品',
        8 => '皮革/皮草',
        9 => '其他面料',
        10 => '辅料',
        11 => '加工服务',
    ];

    /**
     * 热门标签获取
     * @author Nihuan
     * @RequestMapping()
     * @return array
     */
    public function get_list()
    {
        $top_link = [];
        $key = '@RECOMMEND_HOT_TAG_';
        for ($i = 1;$i<= 11;$i++){
            if($this->redis->exists($key . $i)){
                $tag_list = $this->redis->get($key . $i);
                $tag_list = json_decode($tag_list,true);
                $top_key = $this->top_list[$i];
                $tag_list_string = implode(',',$tag_list);
                $string = "[{$tag_list_string}]";
                $top_link[$top_key] = $string;
            }
        }
        return compact("top_link");
    }


    /**
     * 热门标签获取
     * @author Nihuan
     * @RequestMapping()
     * @return array
     */
    public function oss_test()
    {
        $key = '/Buy/169492_2147483647_710.jpg';
        $top_link = get_img_url($key);
        return compact("top_link");
    }


    /**
     * 获取邀请报价记录
     * author: nihuan
     * @param Request $request
     * @return array
     */
    public function get_invite_record(Request $request)
    {
        $date = $request->post('date');
        $historyIndex = '@RecommendMsgHistory_';
        $key_list = [];
        if($this->redis->exists($historyIndex . $date)){
            $key_list = $this->redis->smembers($historyIndex . $date);
        }
        $buy_list = [];
        if(!empty($key_list)){
            foreach ($key_list as $item) {
                $buy = explode('#',$item);
                $buy_list[] = $buy[1];
            }
        }
        $buy_list = array_unique($buy_list);
        $total_buy_list = array_values($buy_list);
        return compact('total_buy_list','key_list');
    }
}