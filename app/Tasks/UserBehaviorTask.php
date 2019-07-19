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
use App\Models\Data\TagData;
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
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

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
     * @var TagData
     */
    private $tagData;

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


    private $behavior_keyword_key = 'behavior_keyword:';

    private $cart_score = 3;//1.加购过产品      行为权重：3
    private $buy_score = 3;//2.发布过采购    行为权重：3
    private $collection_score = 2;//3.收藏过产品      行为权重：2
    private $search_score = 1;//4.搜索过关键词     行为权重：1
    private $records_score = 1;//5.浏览过详情      行为权重：1

    /**
     * 记录用户行为分数,每天1点执行
     * @author yang
     * @Scheduled(cron="1 * * * * *")
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function Behavior()
    {
        write_log(2,'计算用户行为分数开始');
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
            write_log(2,'计算用户行为开始时间:'.$start_time);
            //查询一月内的行为
            //1.加购过产品
            write_log(2,'1');
            $where = [
                'user_id'=>$user_id,
                ['add_time','>',$start_time]
            ];
            $result = $this->orderCartData->getList($where,['pro_id','add_time']);
            if (!empty($result)){
                write_log(2,'计算加购过产品');
                $pro_ids = [];
                foreach ($result as $it) {
                    $pro_ids[] = $it['proId'];
                }
                $where = ['pro_id'=>$pro_ids];
                $names = $this->proData->getUserProductNames($where);
                foreach ($result as $it) {
                    $param = [
                        'keyword'=>$names[$it['proId']],
                        'time'=>$it['addTime'],
                        'score'=>$this->cart_score,
                    ];
                    $this->cache_score($user_id,$param);
                }
            }

            //2.发布过采购
            write_log(2,'2');
            $where = [
                'user_id'=>$user_id,
                ['add_time','>',$start_time]
            ];
            $result = $this->buyData->getBuyList($where,['remark','add_time']);
            if (!empty($result)){
                write_log(2,'计算发布过采购');
                foreach ($result as $it) {
                    $param = [
                        'keyword'=>$it['remark'],
                        'time'=>$it['addTime'],
                        'score'=>$this->buy_score,
                    ];
                    $this->cache_score($user_id,$param);
                }
            }

            //3.收藏过产品
            write_log(2,'3');
            $where = [
                'user_id'=>$user_id,
                'collect_type'=>1,
                'collect_status'=>1,
                ['record_time','>',$start_time],
            ];
            $result = $this->collectionBuriedData->getBuyList($where,['public_id','record_time']);
            if (!empty($result)){
                write_log(2,'计算收藏过产品');
                $pro_ids = [];
                foreach ($result as $it) {
                    $pro_ids[] = $it['publicId'];
                }
                $where = ['pro_id'=>$pro_ids];
                write_log(2,json_encode($where));
                $names = $this->proData->getUserProductNames($where);
                write_log(2,json_encode($names));
                foreach ($result as $it) {
                    $param = [
                        'keyword'=>$names[$it['publicId']],
                        'time'=>$it['recordTime'],
                        'score'=>$this->collection_score,
                    ];
                    $this->cache_score($user_id,$param);
                }
            }

            //4.搜索过关键词
            write_log(2,'4');
            $where = [
                'user_id'=>$user_id,
                'page_num'=>1,
                ['search_time','>',$start_time],
                ['keyword','!=',''],
            ];
            $this->proData->getProductSearchLogList($where,['keyword','search_time']);
            if (!empty($result)){
                write_log(2,'计算搜索过关键词');
                foreach ($result as $it) {
                    $param = [
                        'keyword'=>$it['keyword'],
                        'time'=>$it['searchTime'],
                        'score'=>$this->search_score,
                    ];
                    $this->cache_score($user_id,$param);
                }
            }

            //5.浏览过的产品详情
            write_log(2,'5');
            $where = [
                'user_id'=>$user_id,
                ['r_time','>',$start_time],
            ];
            $this->proData->getProductRecordsList($where,['pro_id','r_time']);
            if (!empty($result)){
                write_log(2,'计算浏览过的产品详情');
                $pro_ids = [];
                foreach ($result as $it) {
                    $pro_ids[] = $it['proId'];
                }
                $where = ['pro_id'=>$pro_ids];
                $names = $this->proData->getUserProductNames($where);
                foreach ($result as $it) {
                    $param = [
                        'keyword'=>$names[$it['proId']],
                        'time'=>$it['rTime'],
                        'score'=>$this->records_score,
                    ];
                    $this->cache_score($user_id,$param);
                }
            }
        }

        write_log(2,'计算用户行为分数结束');
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
        write_log(2,'用户:'.$user_id.'分词:'.$param['keyword']);
        $participle = $this->participle($param['keyword']);
        write_log(2,'分词结果:'.json_encode($participle));
        if (!empty($participle)){
            $redis_key = $this->behavior_keyword_key.$user_id;
            $tag_key = 'tag_names';

            //tag表缓存
            if ($this->redis->exists($tag_key)){
                $tags = json_decode($this->redis->get($tag_key),true);
                write_log(2,'tag缓存记录:'.json_encode($tags));
            }else{
                $tags = $this->tagData->getTagNames();
                $this->redis->set($tag_key,json_encode($tags));
                $this->redis->expire($tag_key,86300);
                write_log(2,'tag查询记录:'.json_encode($tags));
            }
            write_log(2,'tag记录:'.json_encode($tags));
            //缓存关键词
            foreach ($participle as $item) {
                if (array_search($item,$tags)){
                    write_log(2,'关键词缓存:'.$item);
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
        if (!empty($keyword)){
            /* @var ElasticsearchLogic $elastic_logic */
            $elastic_logic = App::getBean(ElasticsearchLogic::class);
            $tag_list_analyzer = $elastic_logic->tokenAnalyzer($keyword);
            if(isset($tag_list_analyzer['tokens']) && !empty($tag_list_analyzer['tokens'])){
                foreach ($tag_list_analyzer['tokens'] as $analyzer) {
                    $tag_list[] = $analyzer['token'];
                }
                $arr = array_column($tag_list_analyzer['tokens'],'token');
            }
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
        $redis_key = $this->behavior_keyword_key.$user_id;

        if ($this->redis->exists($redis_key)){
            write_log(2,'分数缓存存在');
            //有行为标签,删除过期标签
            $tags = $this->redis->hgetall($redis_key);
            if (!empty($tags)){
                foreach ($tags as $key=>$tag) {
                    $info = json_decode($tag,true);
                    write_log(2,'循环标签:'.$key.'&'.$tag);
                    if ($info['time'] < $start_time){
                        //删除过期redis
                        $this->redis->hdel($redis_key,$key);
                    }
                    //记录最近的时间
                    if ($info['time'] > $new_start_time){
                        $new_start_time = $info['time'];
                    }
                }
            }
        }
        return $new_start_time;
    }
}
