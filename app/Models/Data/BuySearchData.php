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
     * 根据标签推荐与我相关
     * @param array $params
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function recommendByTag(array $params)
    {
        $from = $params['page'] == 0 ? $params['page'] : $params['page'] - 1;
        $size = $params['psize'];
        $tag_index = '@RECOMMEND_HOT_TAG_';
        $last_days = env('ES_RECOMMEND_DAYS');
        $last_time = strtotime("-{$last_days} day");

        //过滤基本信息
        $filter = $this->baseFilter();
        if($params['type'] == 0){
            $user_tag_string = $this->redis->get('user_subscription_tag:' . $params['user_id']);
            $user_tag_list = json_decode($user_tag_string,true);
            if(empty($user_tag_list)){
                $user_tag_list = $this->userDao->getUserTagByUid($params['user_id']);
                if(!empty($user_tag_list)){
                    $this->redis->set('user_subscription_tag:' . $params['user_id'],json_encode($user_tag_list));
                }
            }
            $parent_terms = [];
            $product_terms = [];
            $type_terms = [];
            if(!empty($user_tag_list)){
                foreach ($user_tag_list as $tag){
                    if($this->redis->exists($tag_index . $tag['top_id'])){
                        $tag_list_cache = $this->redis->get($tag_index . $tag['top_id']);
                        $tag_list = json_decode($tag_list_cache,true);
                        if(!in_array($tag['parent_name'],$tag_list)){
                            $parent_terms[] = $tag['parent_id'];
                        }else{
                            $product_terms[] = $tag['tag_name'];
                        }
                    }else{
                        $product_terms[] = $tag['tag_name'];
                    }
                    $type_terms[] = $tag['top_id'];
                }
            }
        }else{
            $user_tag_string = $this->redis->get('user_subscription_tag:' . $params['user_id']);
            $user_tag_list = json_decode($user_tag_string,true);
            if(empty($user_tag_list)){
                $user_tag_list = $this->userDao->getUserTagByUid($params['user_id']);
                if(!empty($user_tag_list)){
                    $this->redis->set('user_subscription_tag:' . $params['user_id'],json_encode($user_tag_list));
                }
            }
            if(!empty($user_tag_list)){
                foreach ($user_tag_list as $tag) {
                    if($tag['top_id'] == $params['type']){
                        if($this->redis->exists($tag_index . $tag['top_id'])){
                            $tag_list_cache = $this->redis->get($tag_index . $tag['top_id']);
                            $tag_list = json_decode($tag_list_cache,true);
                            if(!in_array($tag['parent_name'],$tag_list)){
                                $parent_terms[] = $tag['parent_id'];
                            }else{
                                $product_terms[] = $tag['tag_name'];
                            }
                        }else{
                            $product_terms[] = $tag['tag_name'];
                        }
                        $type_terms[] = $tag['top_id'];
                    }
                }
            }
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
        if(!empty($parent_terms)){
            $new_terms = array_unique($parent_terms);
            foreach ($new_terms as $term) {
                $parent_terms_list[] = ['term' => ['proName_ids' => $term]];
            }
        }
        //三级类过滤
        if(!empty($product_terms)){
            $new_product = array_unique($product_terms);
            foreach ($new_product as $item) {
                $parent_terms_list[] = ['term' => ['labels_normalized' => $item]];
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
                'term' => [
                    '_id' => $params['black_ids']
                ]
            ];
        }

        //发布时间过滤
        if(!isset($params['type']) || $params['type'] == 0){
            $size = 100;
            $filter[] = [
                'range' => [
                    'audit_time' => [
                        'from' => $last_time
                    ]
                ]
            ];
        }
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
}