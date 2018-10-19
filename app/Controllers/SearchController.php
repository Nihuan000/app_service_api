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

    /**
     * 与我相关
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function recommend_by_tags(Request $request)
    {
        $user_id = $request->post('user_id');
        if($user_id == false){
            $code = 0;
            $result = [];
            $msg = '请求参数错误: user_id';
        }else{
            $params = [
                'user_id' => $user_id
            ];
            $module = RECOMMEND_MODULE_NAME;
            /* @var ElasticsearchLogic $elastic_logic */
            $elastic_logic = App::getBean(ElasticsearchLogic::class);
            $list = $elastic_logic->search_events($params,$module);
            $code = $list['status'];
            $result = [];
            if(!empty($list['result'])){
                foreach ($list['result'] as $key => $item) {
                    $result[$key] = $item['_source'];
                    $result[$key]['bid'] = $item['_id'];
                }
            }
            $msg = '获取成功';
        }
        return compact('code','result','msg');
    }
}