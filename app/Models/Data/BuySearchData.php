<?php
/**
 * Created by PhpStorm.
 * Date: 18-10-18
 * Time: 下午2:31
 */

namespace App\Models\Data;

use App\Models\Dao\BuyBuriedDao;
use App\Models\Dao\UserDao;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Bean;
use Swoft\Log\Log;
use Swoft\Redis\Redis;

/**
 * 采购搜索
 * @Bean()
 * @uses      BuySearchData
 * @author    Nihuan
 */
class BuySearchData
{

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * @Inject()
     * @var BuyBuriedDao
     */
    private $buyburiedDao;

    /**
     * 根据标签推荐与我相关
     * @param array $params
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function recommendByTag(array $params)
    {
        $page = $params['page'] == 0 ? $params['page'] : $params['page'] - 1;
        $size = $params['psize'] > 100 ? 100 : $params['psize'];
        $from = $page * $size;
//        $last_days = env('ES_RECOMMEND_DAYS');
//        $last_time = strtotime("-{$last_days} day");

        //过滤基本信息
        $filter = $this->baseFilter();
        $user_tag_string = $this->redis->get('user_subscription_tag:' . $params['user_id']);
        $user_tag_list = json_decode($user_tag_string,true);
        $ttl = $this->redis->ttl('user_subscription_tag:' . $params['user_id']);
        if(empty($user_tag_list) || (!empty($user_tag_list) && $ttl == -1)){
            $user_tag_list = $this->userDao->getUserTagByUid($params['user_id']);
            if(!empty($user_tag_list)){
                //设置过期时间
                $this->redis->setex('user_subscription_tag:' . $params['user_id'],24*3600,json_encode($user_tag_list));
            }
        }
        $product_terms = [];
        $type_terms = [];
        $label_ids = [];
        if($params['type'] == 0){
            if(!empty($user_tag_list)){
                foreach ($user_tag_list as $tag){
                    $product_terms[] = $tag['tag_name'];
                    $type_terms[] = $tag['top_id'];
                }
            }
        }else{
            $type_terms[] = $params['type'];
            $push_newest_key = 'push_newest_time_' . $params['user_id'] . '_' . $params['type'];
            if(!$this->redis->exists($push_newest_key) && $page == 0){
                $push_newest_time = time();
                $this->redis->setex($push_newest_key,120,$push_newest_time);
            }else{
                $push_newest_time = $this->redis->get($push_newest_key);
            }
            if(!empty($user_tag_list)){
                foreach ($user_tag_list as $tag) {
                    if($tag['top_id'] == $params['type']){
                        $product_terms[] = $tag['tag_name'];
                        $label_ids[] = $tag['tag_id'];
                    }
                }
            }
            $filter[] = [
                'range' => [
                    'refresh_time' => [
                        'to' => $push_newest_time
                    ]
                ]
            ];
        }

        $must_not = [];

        //大类过滤
        if(!empty($type_terms)){
            $new_type_list = array_unique($type_terms);
            $type_terms_list = [];
            foreach ($new_type_list as $type) {
                $type_terms_list[] = ['term' => ['type_id' => $type]];
            }
            $filter[] = [
                'bool' => [
                    'should' => $type_terms_list,
                    'minimum_should_match' => 1
                ]
            ];
        }
        //二级类过滤
        $parent_terms_list = [];
        //三级类过滤
        if(!empty($product_terms)){
            $new_product = array_unique($product_terms);
            foreach ($new_product as $item) {
                $parent_terms_list[] = ['term' => ['labels' => $item]];
            }
        }

        $filter[] = [
            'bool' => [
                'should' => $parent_terms_list,
                'minimum_should_match' => 1
            ]
        ];

        //不匹配列表
        if(isset($params['black_ids']) && !empty($params['black_ids'])){
            $must_not[] = [
                'terms' => [
                    '_id' => $params['black_ids']
                ]
            ];
        }

        //发布时间过滤
//        if(!isset($params['type']) || $params['type'] == 0){
//            $filter[] = [
//                'range' => [
//                    'audit_time' => [
//                        'from' => $last_time
//                    ]
//                ]
//            ];
//        }
        //搜索语句拼接
        $query = [
            'from' => $from,
            'size' => $size,
            'query' => [
                'bool' => [
                    'filter' => $filter,
                    'must_not' => $must_not
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
        //记录日志
        $params['label_ids'] = $label_ids;
        $query['search_params'] = $params;
        //搜索执行语句生成
        return $query;
    }

    /**
     * 采购推送判断索引是否已存在
     * @param int $buy_id
     * @return array
     */
    public function recommendCheckInfoSync(int $buy_id)
    {
        $query = [];
        $filter = $this->baseFilter();
        if($buy_id > 0)
        {
            //过滤基本信息
            $filter[] = [
                'term' => [
                    '_id' => $buy_id
                ]
            ];
            $query = [
                'size' => 0,
                'query' => [
                    'bool' => [
                        'filter' => $filter,
                    ]
                ]
            ];
        }
        Log::info(json_encode($query));
        return $query;
    }

    /**
     * 搜索返回字段
     * @author Nihuan
     * @return array
     */
    private function searchSource()
    {
        return [ "buy_id", "amount", "status", "unit", "type", "pic", "remark", "province", "city", "role", "add_time", "alter_time", "is_customize", "earnest", "buy_fixed", "fixed_amount","del_status","audit_time"];
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

    /**
     * 搜索日志添加
     * @param $params
     * @param int $hot_type
     */
    public function search_log($params,$hot_type = 6)
    {
        $buyLog = [
            'user_id' => $params['user_id'],
            'parentid' => $params['type'],
            'label_ids' => json_encode($params['label_ids']),
            'is_hot' => $hot_type,
            'match_num' => $params['match_num'],
            'match_ids' => $params['match_ids'],
            'request_id' => $params['request_id'],
            'page_num' => $params['page'],
            'version' => $params['version']
        ];
        $this->buyburiedDao->saveSearchBuried($buyLog);
    }
}
