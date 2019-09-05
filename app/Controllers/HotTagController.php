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
        $total_count = count($key_list);
        $buy_count = count($total_buy_list);
        return compact('total_buy_list','total_count','buy_count');
    }
}
