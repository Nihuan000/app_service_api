<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-9-4
 * Time: 上午10:53
 */

namespace App\Controllers;

use App\Models\Logic\ElasticsearchLogic;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\App;
use Swoft\Http\Message\Server\Request;

/**
 * Class SearchController
 * @Controller()
 */
class SearchController
{
    /**
     * 指定时间内刷新采购数查询
     * @RequestMapping()
     * @author Nihuan
     * @param Request $request
     * @return array
     */
    public function buy_refresh_count(Request $request)
    {
        //关键词
        $last_view_time = $request->post('last_view_time');
        /* @var ElasticsearchLogic $elastic_logic */
        $elastic_logic = App::getBean(ElasticsearchLogic::class);
        $searchRes = $elastic_logic->getRefreshCount($last_view_time);
        $code = (int)$searchRes['code'];
        $msg = '获取成功';
        $result = [
            'buy_count' => $searchRes['count'],
        ];
        return compact('code','result','msg');
    }
}