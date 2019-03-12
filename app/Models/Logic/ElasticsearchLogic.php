<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-25
 * Time: 下午3:31
 */

namespace App\Models\Logic;

use App\Models\Data\BuySearchData;
use App\Models\Data\TagData;
use App\Pool\Config\ElasticsearchPoolConfig;
use Elasticsearch\ClientBuilder;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Bean;
use App\Pool\ElasticsearchPool;
use Swoft\Exception\PoolException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;

/**
 * 采购搜索
 * 同时可以被controller server task使用
 * @Bean()
 * @uses      ElasticsearchLogic
 * @version   1.0
 * @author    Nihuan
 */
class ElasticsearchLogic
{
    /**
     * @Inject()
     * @var ElasticsearchPoolConfig
     */
    public $esConfig;

    /**
     * @Inject()
     * @var BuySearchData
     */
    public $buySearchData;

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    public $redis;

    /**
     * @Inject()
     * @var TagData
     */
    public $tagData;

    /**
     * 单连接池
     * @author Nihuan
     * @return \Elasticsearch\Client
     * @throws PoolException
     */
    public function simpleConnectionPool()
    {
        if (empty($this->esConfig)) {
            throw new PoolException('You must to set elasticPoolConfig by @Inject!');
        }
        $client = ClientBuilder::create()
            ->setConnectionPool('\Elasticsearch\ConnectionPool\SimpleConnectionPool',[])
            ->setHosts($this->esConfig->getUri())->build();
        return $client;
    }

    /**
     * 搜索处理
     * @param array $events
     * @param string $module
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function search_events(array $events, string $module)
    {
        $master_name = '';
        $type = '';
        $query = [];
        $status = 0;
        switch ($module){
            case RECOMMEND_MODULE_NAME:
                $master_name = $this->esConfig->getBuyMaster();
                $query = $this->buySearchData->recommendByTag($events);
                $type = 'buy';
                break;
        }
        if(!empty($master_name) && !empty($type)){
            //搜索执行语句生成
            $params = [
                'index' => $master_name,
                'type' => 'buy',
                'body' => $query,
            ];
            try {
                $connect = $this->simpleConnectionPool();
                $result = $connect->search($params);
                if(!empty($result)){
                    $list = $result['hits']['hits'];
                    $list = $this->_DataEntity($list);
                    $count = (int)$result['hits']['total'];
                    return ['status' => 200, 'result' => ['list' => $list, 'count' => $count]];
                }
            } catch (PoolException $e) {
                print_r($e->getMessage());
            }
        }
        return ['status' => $status,'list' => []];
    }

    /**
     * 验证索引是否已生成
     * @param array $params
     * @return int
     */
    public function checkDataExists(array $params)
    {
        $count = 0;
        $master_name = $this->esConfig->getBuyMaster();
        if(!empty($params)){
            $query = $this->buySearchData->recommendCheckInfoSync($params['buyId']);
            //搜索执行语句生成
            $indexParams = [
                'index' => $master_name,
                'type' => 'buy',
                'body' => $query,
            ];
            try {
                $connect = $this->simpleConnectionPool();
                $result = $connect->search($indexParams);
                if(!empty($result)){
                    $count = (int)$result['hits']['total'];
                }
            } catch (PoolException $e) {
                print_r($e->getMessage());
            }
        }
        $pushRes = 0;
        if($count > 0){
            //处理推送请求
            $pushParams = [
                'serviceName' => env('RECOMMEND_SERVICENAME'),
                'methodName' => env('RECOMMEND_METHODNAME'),
                'applicationName' => env('RECOMMEND_APPNAME'),
                'inputParameter' => $params
            ];
            $params_json = json_encode($pushParams);
            $service_url = env('SEARCH_HOST');
            $header_data = [
                'Content-Type: application/json',
            ];
            $pushAction = [
                'url' => $service_url,
                'timeout' => 5,
                'headers' => $header_data,
                'post_params' => $params_json
            ];
            Log::info(json_encode($pushAction));
            $pushResponse = CURL($pushAction,'post');
            $push_arr = json_decode($pushResponse,true);
            Log::info("推送结果: {$pushResponse}");
            if($push_arr['code'] == 0 && in_array($push_arr['data']['status'],[1,2])){
                $pushRes = 1;
            }
        }else{
            //队列回写
            $date = date('Y-m-d');
            $index = '@RecommendPushQueue_';
            $msg_json = json_encode($params);
            $this->redis->rPush($index . $date, $msg_json);
            $pushRes = 2;
        }
        return $pushRes;
    }

    /**
     * 推送写入队列
     * @param array $params
     * @return bool|int
     */
    public function push_buy_to_queue(array $params)
    {
        $queue_res = true;
        $date = date('Y-m-d');
        $index = '@RecommendPushQueue_';
        $historyIndex = '@RecommendPushHistory_';
        $checkHistory = $this->redis->exists($historyIndex . $date);
        $msg_json = json_encode($params);
        $history = false;
        if($checkHistory){
            $historyExists = $this->redis->sIsMember($historyIndex . $date, $params['buyId']);
            if($historyExists == false){
                $waitHistory = $this->redis->lrange($index . $date,0,-1);
                if(!empty($waitHistory)){
                    if(in_array($msg_json,$waitHistory)){
                        $history = true;
                    }
                }
            }else{
                $history = true;
            }
        }
        if($history == false){
            $queue_res = $this->redis->rPush($index . $date, $msg_json);
        }
        return $queue_res;
    }

    /**
     * 分词处理
     * @param $text
     * @return array
     */
    public function tokenAnalyzer($text)
    {
        $master_name = $this->esConfig->getBuyMaster();
        $params = [
            'index' => $master_name,
            'body' => [
                'text' => $text,
                'analyzer'=>'ik_smart'
            ]
        ];

        try {
            $connect = $this->simpleConnectionPool();
            $result = $connect->indices()->analyze($params);
            if(!empty($result)){
                return $result;
            }
        } catch (PoolException $e) {
            print_r($e->getMessage());
        }
    }

    /**
     * 标签分词
     * @param $text
     * @return array
     */
    public function tagAnalyzer($text)
    {
        $match_list = [];
        $cache_keys = 'product_name_tag_dict';
        if($this->redis->exists($cache_keys)){
            $tag_cache = $this->redis->get($cache_keys);
            $tag_list = json_decode($tag_cache,true);
        }
        if(empty($tag_list)){
            $tag_list = [];
            $tag_data = $this->tagData->getTagListByCate(1);
            if(!empty($tag_data)){
                foreach ($tag_data as $item) {
                    $tag_list[] = $item['name'];
                }
            }
            $this->redis->set($cache_keys,json_encode($tag_list));
        }
        if(!empty($tag_list)){
            foreach ($tag_list as $item) {
                if(!empty($item) && strstr($text,$item)){
                    $match_list[] = $item;
                }
            }
        }
        return $match_list;
    }

    /**
     * 自动报价产品标签获取
     * @param $pro_id
     * @return array|mixed
     */
    public function offerProAnalyzer($pro_id)
    {
        $tag_list = [];
        $cache_keys = 'product_name_tag_dict';
        if($this->redis->exists($cache_keys)){
            $tag_cache = $this->redis->get($cache_keys);
            $tag_list = json_decode($tag_cache,true);
        }
        if(empty($tag_list)){
            $tag_list = [];
            $tag_data = $this->tagData->getTagListByProId($pro_id);
            if(!empty($tag_data)){
                foreach ($tag_data as $item) {
                    $tag_list[] = $item['tagName'];
                }
            }
            $this->redis->set($cache_keys,json_encode($tag_list));
        }
        return $tag_list;
    }

    /**
     * 采购列表
     * author: nihuan
     * @param int $last_time 最后请求时间
     * @return array
     */
    public function getRefreshCount(int $last_time)
    {
        $count = 0;
        $status = 0;
        $master_name = $this->esConfig->getBuyMaster();
        //过滤基本信息
        $filter = $this->baseFilter();
        //刷新时间过滤
        $filter[] = [
            'range' => [
                'refresh_time' => [
                    'from' => $last_time
                ]
            ]
        ];
        //搜索语句拼接
        $query = [
            'query' => [
                'bool' => [
                    'filter' => $filter,
                ]
            ],
            '_source' => [
                'includes' => $this->searchSource(),
            ],
        ];
        //搜索执行语句生成
        $params = [
            'index' => $master_name,
            'type' => 'buy',
            'body' => $query,
        ];
        try {
            $connect = $this->simpleConnectionPool();
            $result = $connect->search($params);
            if(!empty($result)){
                $count = $result['hits']['total'];
            }
            $status = 200;
        } catch (PoolException $e) {
            print_r($e->getMessage());
        }
        return ['count' => $count, 'code' => $status];
    }

    /**
     * 搜索返回字段
     * @author Nihuan
     * @return array
     */
    private function searchSource()
    {
        return [ "refresh_time" ];
    }


    /**
     * 基本信息过滤
     * author: nihuan
     * @return array
     */
    private function baseFilter()
    {
        //过滤基本信息, 采购状态/审核通过/删除状态/上线状态/数量
        $filter = [
            [
                'term' => [
                    'del_status' => 1
                ]
            ],
            [
                'term' => [
                    'is_audit' => 0
                ]
            ],
            [
                'term' => [
                    'forbid' => "0"
                ]
            ],
            [
                'term' => [
                    'status' => 0
                ]
            ],
            [
                'range' => [
                    'amount' => [
                        'from' => 1
                    ]
                ]
            ]
        ];
        return $filter;
    }

    /**
     * 字段类型格式化
     * @param $data
     * @return mixed
     */
    private function _DataEntity($data)
    {
        $list = [];
        foreach ($data as $key => $sub) {
            if(isset($sub['_id'])){
                $list[$key]['bid'] = (int)$sub['_id'];
            }
            $item = $sub['_source'];
            if(isset($item['status'])){
                $list[$key]['status'] = (int)$item['status'];
            }
            if(isset($item['type'])){
                $list[$key]['type'] = (int)$item['type'];
            }
            if(isset($item['buy_fixed'])){
                $list[$key]['buy_fixed'] = (int)$item['buy_fixed'];
            }
            if(isset($item['amount'])){
                $list[$key]['amount'] = (int)$item['amount'];
            }
            if(isset($item['is_customize'])){
                $list[$key]['is_customize'] = (int)$item['is_customize'];
            }
            if(isset($item['del_status'])){
                $list[$key]['del_status'] = (int)$item['del_status'];
            }
            if(isset($item['audit_time'])){
                $list[$key]['add_time'] = (int)$item['audit_time'];
            }
            if(isset($item['add_time'])){
                $list[$key]['look_ahead_time'] = (int)$item['add_time'];
            }
            if(isset($item['audit_time'])){
                $list[$key]['pushTime'] = (int)$item['audit_time'];
            }
            if(isset($item['del_status'])){
                $list[$key]['del_status'] = (int)$item['del_status'];
            }
            if(isset($item['remark'])){
                $list[$key]['remark'] = $item['remark'];
            }
            if(isset($item['pic'])){
                $list[$key]['pic'] = $item['pic'];
            }
            if(isset($item['city'])){
                $list[$key]['city'] = $item['city'];
            }
            if(isset($item['unit'])){
                $list[$key]['unit'] = $item['unit'];
            }
            if(isset($item['fixed_amount'])){
                $list[$key]['fixed_amount'] = $item['fixed_amount'];
            }
            if(isset($item['province'])){
                $list[$key]['province'] = $item['province'];
            }
        }
        return $list;
    }
}