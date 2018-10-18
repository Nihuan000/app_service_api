<?php
/**
 * Created by PhpStorm.
 * Date: 18-10-18
 * Time: 下午2:31
 */

namespace App\Models\Data;

use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Bean;
use Swoft\Exception\PoolException;

/**
 * 采购搜索
 * 同时可以被controller server task使用
 * @Bean()
 * @uses      ElasticsearchLogic
 * @version   1.0
 * @author    Nihuan
 */
class BuySearchData
{

    /**
     * 根据标签推荐与我相关
     * @param array $params
     * @return array
     */
    public function recommendByTag(array $params)
    {
        $last_days = env('ES_RECOMMEND_DAYS');
        $last_time = strtotime("-{$last_days} day");
        //过滤基本信息
        $filter = $this->baseFilter();
        //标签过滤
        $filter[] = [
            'terms' => [
                'proName_ids' => $params['event']
            ]
        ];
        //发布时间过滤
        $filter[] = [
            'range' => [
                'add_time' => [
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
        return $query;
    }

    /**
     * 搜索返回字段
     * @author Nihuan
     * @return array
     */
    private function searchSource()
    {
        return [ "buy_id", "amount", "status", "unit", "type", "pic", "remark", "province", "city", "role", "add_time", "alter_time", "is_customize", "earnest", "buy_fixed", "fixed_amount"];
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
                'bool' => [
                    'should' => [
                        [
                            'term' => [
                                'is_audit' => 0
                            ]
                        ],
                        [
                            'term' => [
                                'is_audit' => 5
                            ]
                        ]
                    ],
                    'minimum_should_match' => 1
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
}