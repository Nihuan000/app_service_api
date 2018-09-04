<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-25
 * Time: 下午3:31
 */

namespace App\Models\Logic;
use App\Pool\ElasticsearchPool;
use Swoft\Exception\PoolException;

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
     * @var ElasticsearchPool
     */
    public $esPool;

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
        $master_name = $this->esPool->poolConfig->getBuyMaster();
        //过滤基本信息
        $filter = $this->baseFilter();
        //刷新时间过滤
        $filter[] = [
            [
                'range' => [
                    'refresh_time' => [
                        'from' => $last_time
                    ]
                ]
            ]
        ];
        //搜索语句拼接
        $query = [
            'filter' => $filter,
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
            $result = $this->esPool->search($params);
            if(!empty($result)){
                $count = $result['hits']['total'];
            }
            $status = 1;
        } catch (PoolException $e) {
            print_r($e->getMessage());
        }
        return ['count' => $count, 'status' => $status];
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
                    'type_id' => 2
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
}