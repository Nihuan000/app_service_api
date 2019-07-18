<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Tasks;

use App\Models\Data\BuyData;
use App\Models\Data\OrderCartData;
use App\Models\Data\ProductData;
use App\Models\Data\UserData;
use App\Models\Data\CollectionBuriedData;
use App\Models\Logic\ElasticsearchLogic;
use Swoft\App;
 use Swoft\Bean\Annotation\Inject;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class OfferTask - define some tasks
 *
 * @Task("UserBehavior")
 * @package App\Tasks
 */
class UserBehaviorTask{
    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $searchRedis;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @Inject()
     * @var BuyData
     */
    private $buyData;

    /**
     * @Inject()
     * @var ProductData
     */
    private $proData;

    /**
     * @Inject()
     * @var OrderCartData
     */
    private $orderCartData;

    /**
     * @Inject()
     * @var CollectionBuriedData
     */
    private $collectionBuriedData;

    private $cart_score = 3;//1.加购过产品      行为权重：3
    private $buy_score = 3;//2.发布过采购    行为权重：3
    private $collection_score = 2;//3.收藏过产品      行为权重：2
    private $search_score = 1;//4.搜索过关键词     行为权重：1
    private $records_score = 1;//5.浏览过详情      行为权重：1

    /**
     * 记录用户行为分数,每天1点执行
     * @author yang
     * @Scheduled(cron="0 1 * * * *")
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function Behavior()
    {
        //昨天有登录记录的采购商
        $where = [
            //['last_time','>',strtotime('-1 day')],
            //'role'=>[2,3,4],
            'user_id'=>173010,
        ];
        $user_ids = $this->userData->getUserDataByParams($where,100000);

        foreach ($user_ids as $item) {
            $user_id = $item['userId'];

            //过期一月前的行为
            $start_time = $this->del_expire_tag($user_id,strtotime('-1 month'));

            //查询一月内的行为
            //1.加购过产品
            $where = [
                'user_id'=>$user_id,
                ['add_time','>',$start_time]
            ];
            $result = $this->orderCartData->getList($where,['pro_id','add_time']);
            if (!empty($result)){
                $where = ['pro_id'=>array_column($result,'pro_id')];
                $names = $this->proData->getUserProductNames($where);
                foreach ($result as $it) {
                    $param = [
                        'keyword'=>$names[$it['pro_id']],
                        'time'=>$it['add_time'],
                        'score'=>$this->cart_score,
                    ];
                    $this->cache_score($user_id,$param);
                }
            }

            //2.发布过采购
            $where = [
                'user_id'=>$user_id,
                ['add_time','>',$start_time]
            ];
            $result = $this->buyData->getBuyList($where,['remark','add_time']);
            if (!empty($result)){
                foreach ($result as $it) {
                    $param = [
                        'keyword'=>$it['remark'],
                        'time'=>$it['add_time'],
                        'score'=>$this->buy_score,
                    ];
                    $this->cache_score($user_id,$param);
                }
            }

            //3.收藏过产品
            $where = [
                'user_id'=>$user_id,
                'collect_type'=>1,
                'collect_status'=>1,
                ['record_time','>',$start_time],
            ];
            $result = $this->collectionBuriedData->getBuyList($where,['public_id','add_time']);
            if (!empty($result)){
                
                $where = ['pro_id'=>array_column($result,'public_id')];
                $names = $this->proData->getUserProductNames($where);

                foreach ($result as $it) {
                    $param = [
                        'keyword'=>$names[$it['public_id']],
                        'time'=>$it['add_time'],
                        'score'=>$this->collection_score,
                    ];
                    $this->cache_score($user_id,$param);
                }
            }

            //4.搜索过关键词
            $where = [
                'user_id'=>$user_id,
                'page_num'=>1,
                ['search_time','>',$start_time],
                ['keyword','!=',''],
            ];
            $this->proData->getProductSearchLogList($where,['keyword','search_time']);
            if (!empty($result)){
                foreach ($result as $it) {
                    $param = [
                        'keyword'=>$it['keyword'],
                        'time'=>$it['search_time'],
                        'score'=>$this->search_score,
                    ];
                    $this->cache_score($user_id,$param);
                }
            }

            //5.浏览过详情
            $where = [
                'user_id'=>$user_id,
                ['r_time','>',$start_time],
            ];
            $this->proData->getProductRecordsList($where,['pro_id','r_time']);
            if (!empty($result)){

                $where = ['pro_id'=>array_column($result,'pro_id')];
                $names = $this->proData->getUserProductNames($where);
                foreach ($result as $it) {
                    $param = [
                        'keyword'=>$names[$it['pro_id']],
                        'time'=>$it['r_time'],
                        'score'=>$this->records_score,
                    ];
                    $this->cache_score($user_id,$param);
                }
            }
        }
    }
    /**
     * 分词并缓存分数
     * @author yang
     * @date 19-7-16
     * @param int $user_id
     * @param array $param
     */
    private function cache_score($user_id,$param)
    {
        $participle = $this->participle($param['keyword']);
        if (!empty($participle)){
            $redis_key = 'behavior_keyword:'.$user_id;
            $tag_key = 'tag_names';

            //tag表缓存
            if ($this->redis->exist($tag_key)){
                $tags = json_decode($this->redis->get($tag_key),true);
            }else{
                $tags = M('tag')->getField('name',true);
                $this->redis->set($tag_key,json_encode($tags));
                $this->redis->expire($tag_key,86300);
            }

            //缓存关键词
            foreach ($participle as $item) {
                if (array_search($tags,$item)){
                    $value = json_encode(['time'=>$param['time'],'score'=>$param['score']]);
                    $this->redis->hset($redis_key,$item,$value);
                }
            }
        }
    }

    /**
     * 分词处理
     * @param string $keyword
     * @return array
     */
    private function participle($keyword)
    {
        $arr = [];
        /* @var ElasticsearchLogic $elastic_logic */
        $elastic_logic = App::getBean(ElasticsearchLogic::class);
        $tag_list_analyzer = $elastic_logic->tokenAnalyzer($keyword);
        if(isset($tag_list_analyzer['tokens']) && !empty($tag_list_analyzer['tokens'])){
            foreach ($tag_list_analyzer['tokens'] as $analyzer) {
                $tag_list[] = $analyzer['token'];
            }
            $arr = array_column($tag_list_analyzer['tokens'],'token');
        }
        return $arr;
    }

    /**
     * 删除过期标签
     * @param int $user_id
     * @param int $start_time
     * @return int
     */
    private function del_expire_tag($user_id,$start_time)
    {
        //过期一月前的行为
        $new_start_time = $start_time;
        $redis_key = 'behavior_keyword:'.$user_id;

        if ($this->searchRedis->exists($redis_key)){
            //有行为标签,删除过期标签
            $tags = $this->searchRedis->hgetall($redis_key);
            if (!empty($tags)){
                foreach ($tags as $key=>$tag) {
                    $info = json_decode($tag,true);
                    if ($info['time'] < $start_time){
                        //删除过期redis
                        $this->searchRedis->hdel($redis_key,$key);
                        //记录最近的时间
                        if ($info['time'] > $new_start_time){
                            $new_start_time = $info['time'];
                        }
                    }
                }
            }
        }
        return $new_start_time;
    }
}
