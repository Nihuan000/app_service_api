<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-9-4
 * Time: 上午10:53
 */

namespace App\Controllers;

use App\Models\Data\ProductData;
use App\Models\Data\TbPushBuyRecordData;
use App\Models\Data\UserData;
use App\Models\Logic\ElasticsearchLogic;
use App\Models\Logic\UserLogic;
use Swoft\Bean\Annotation\Inject;
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
     * @Inject()
     * @var TbPushBuyRecordData
     */
    protected $pushBuyData;
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
        $type = $request->post('type',0);
        $black_ids = $request->post('black_list');
        if($user_id == false){
            $code = 0;
            $result = [];
            $msg = '请求参数错误: user_id';
        }else{
            $params = [
                'user_id' => $user_id,
                'type' => $type,
                'black_ids' => $black_ids
            ];
            $module = RECOMMEND_MODULE_NAME;
            /* @var ElasticsearchLogic $elastic_logic */
            $elastic_logic = App::getBean(ElasticsearchLogic::class);
            $list = $elastic_logic->search_events($params,$module);
            $code = $list['status'];
            $result = [
                'list' => [],
                'count' => 0
            ];
            $result['count'] = $list['result']['count'];
            $result['list'] = is_null($list['result']['list']) ? [] : $list['result']['list'];
            $msg = '获取成功';
        }
        return compact('code','result','msg');
    }

    /**
     * 商机推荐状态修改
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function update_push_status(Request $request)
    {
        $user_id = $request->post('user_id');
        $buy_id = $request->post('buy_id');
        $status = $request->post('status');
        if($user_id == false || $buy_id == false || $status === false){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $params = [
                'user_id' => $user_id,
                'buy_id' => $buy_id
            ];
            $record = $this->pushBuyData->getUserPushRecord($params);
            if(!empty($record)){
                $update['is_read'] = $status;
                $update['update_time'] = (int)microtime(1) * 1000;
                $dataRes = $this->pushBuyData->updatePushRecord($record['id'],$update);
            }else{
                $insert['buy_id'] = $buy_id;
                $insert['user_id'] = $user_id;
                $insert['is_read'] = $status;
                $dataRes = $this->pushBuyData->insertPushRecord($insert);
            }
            if($dataRes){
                $statusRes = 0;
            }else{
                $statusRes = 1;
            }
            $code = 200;
            $result = ['status' => $statusRes];
            $msg = '修改成功';
        }
        return compact('code','result','msg');
    }

    /**
     * 瀑布流列表
     * @param Request $request
     * @return array
     */
    public function waterfalls_product(Request $request)
    {
        $cycle = $request->post('cycle_num');
        $display_count = $request->post('display_count');
        $pages = $request->post('page',1);
        $pageSize = $request->post('pageSize',20);
        if(empty($cycle) || empty($display_count)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $params = [
                'cycle' => $cycle,
                'display_count' => $display_count,
                'page' => $pages,
                'psize' => $pageSize
            ];
            /* @var ProductData $proLogic */
            $proLogic = App::getBean(ProductData::class);
            $searchRes = $proLogic->getIndexWaterfalls($params);
            if(!empty($searchRes)){
                $code = 200;
                $result = ['list' => $searchRes];
                $msg = '获取成功';
            }else{
                $code = 0;
                $result = [];
                $msg = '数据获取失败';
            }
        }
        return compact('code','result','msg');
    }

    /**
     * 进货资格判断
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function purchase_judgment(Request $request)
    {
        $user_id = $request->post('user_id');
        $tag_id = $request->post('tag_id');
        if($user_id == false ){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $params = [
                'user_id' => $user_id,
                'tag_id' => $tag_id
            ];
            /* @var UserLogic $userLogic */
            $userLogic = App::getBean(UserLogic::class);
            $result = $userLogic->checkUserTagExists($params);
            if($result !== false){
                $code = 200;
                $result = ['is_meet' => $result];
                $msg = '获取成功';
            }else{
                $code = 0;
                $result = [];
                $msg = '获取失败';
            }
        }
        return compact('code','result','msg');
    }
}