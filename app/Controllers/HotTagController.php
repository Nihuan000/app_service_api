<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-27
 * Time: 上午11:30
 */

namespace App\Controllers;

use Swoft\Bean\Annotation\Inject;
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
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => '',
        6 => '',
        7 => '',
        8 => '',
        9 => '',
        10 => '',
        11 => '',
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
        $key = 'RECOMMEND_HOT_TAG_';
        for ($i = 1;$i<= 11;$i++){
            if($this->redis->exists($key . $i)){
                $tag_list = $this->redis->get($key . $i);
                $tag_list = json_decode(unserialize($tag_list));
                $top_key = $this->top_list[$i];
                $tag_list_string = implode(',',$tag_list);
                $string = "[{$tag_list_string}]";
                $top_link[$top_key] = $string;
            }
        }
        return compact("top_link");
    }
}