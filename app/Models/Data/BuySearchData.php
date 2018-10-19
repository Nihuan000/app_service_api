<?php
/**
 * Created by PhpStorm.
 * Date: 18-10-18
 * Time: 下午2:31
 */

namespace App\Models\Data;

use App\Models\Dao\UserDao;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Bean;
use Swoft\Exception\PoolException;
use Swoft\Redis\Redis;

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
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;
    /**
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * 根据标签推荐与我相关
     * @param array $params
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function recommendByTag(array $params)
    {
        $size = 100;
        $tag_index = '@RECOMMEND_HOT_TAG_';
        $last_days = env('ES_RECOMMEND_DAYS');
        $last_time = strtotime("-{$last_days} day");

        //过滤基本信息
        $filter = $this->baseFilter();

        $user_tag_list = $this->userDao->getUserTagByUid($params['user_id']);
        $parent_terms = [];
        $type_terms = [];
        if(!empty($user_tag_list)){
            foreach ($user_tag_list as $tag){
                if($this->redis->exists($tag_index . $tag['main_type'])){
                    $tag_list_cache = $this->redis->get($tag_index . $tag['main_type']);
                    $tag_list = json_decode($tag_list_cache,true);
                    if(!in_array($tag['sec_category'],$tag_list)){
                        $parent_terms[] = [
                            'term' => ['proName_ids' => $tag['sec_cateid']]
                        ];
                    }else{
                        $parent_terms[] = [
                            'term' => ['labels_normalized' => $tag['name']]
                        ];
                    }
                }
                $type_terms[] = [
                    'term' => ['type_id' => $tag['main_type']]
                ];
            }
        }
        //大类过滤
        if(!empty($type_terms)){
            $filter[] = [
                'bool' => [
                    'should' => $type_terms,
                    'minimum_should_match' => 1
                ]
            ];
        }
        //二级类过滤
        if(!empty($parent_terms)){
            $filter[] = [
                'bool' => [
                    'should' => $parent_terms,
                    'minimum_should_match' => 1
                ]
            ];
        }

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
            'size' => $size,
            'query' => [
                'bool' => [
                    'filter' => $filter
                ]
            ],
            '_source' => [
                'includes' => $this->searchSource(),
            ],
            'sort' => [
                'refresh_time' => [
                    'order' => 'desc'
                ]
            ]
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