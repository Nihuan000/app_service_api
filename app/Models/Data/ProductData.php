<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Data;

use App\Models\Dao\ProductDao;
use function Couchbase\defaultDecoder;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Task;

/**
 * 产品数据类
 * @Bean()
 * @uses      ProductData
 * @author    Nihuan
 */
class ProductData
{

    /**
     * 产品数据对象
     * @Inject()
     * @var ProductDao
     */
    private $productDao;

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    protected $pro_cate = [
        2 => '辅料',
        5 => '针织',
        7 => '蕾丝/绣品',
        8 => '皮革/皮草',
        9 => '其他',
        10 => '棉类',
        11 => '麻类',
        12 => '呢料毛纺',
        13 => '丝绸/真丝',
        14 => '化纤',
    ];

    /**
     * 时间维度
     * @var array
     */
    protected $timeRank = [86400 => 10000,604800 => 100];

    /**
     * 内部用户
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @param $user_id
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserVisitProduct($user_id)
    {
        $product_tag = [];
        $last_time = strtotime('-1 month');
        $visit_list = $this->productDao->getUserProductVisitLog($user_id, $last_time);
        if(!empty($visit_list)){
            $pro_ids = [];
            $pro_id_time = [];
            foreach ($visit_list as $item) {
                $pro_ids[] = $item['pro_id'];
                $pro_id_time[$item['pro_id']] = $item['r_time'];
            }
            $fields = ['type','pro_id'];
            $product_types = $this->productDao->getProductTypeList($pro_ids,$fields);
            if(!empty($product_types)){
                $now_time = time();
                foreach ($product_types as $product_type) {
                    $type_name = isset($this->pro_cate[$product_type['type']]) ? $this->pro_cate[$product_type['type']] : '';
                    if(!empty($type_name)){
                        $tag_score = 1;
                        $tag_mictime = $now_time - $pro_id_time[$product_type['proId']];
                        $value_add = similar_acquisition($tag_mictime,$this->timeRank);
                        if(!empty($value_add)){
                            $tag_score *= $value_add;
                        }
                        $product_tag[$type_name][] = $tag_score;
                    }
                }
            }
        }
        return $product_tag;
    }

    /**
     * 实力好店刷新
     * @param array $params
     * @return bool
     * @throws \Swoft\Task\Exception\TaskException
     */
    public function preferredShop(array $params)
    {
        Task::deliver('PopularTag','refreshPreferredShop',[$params['url'], $params['user_id']], Task::TYPE_ASYNC);
        return true;
    }

    /**
     * 首页瀑布流数据缓存获取
     * @param array $params
     * @return array
     * @throws \Swoft\Task\Exception\TaskException
     */
    public function getIndexWaterfalls(array $params)
    {
        $waterfall_index = 'index_water_falls_list_' . $params['cycle'] . '_' . $params['display_count'];
        if($this->redis->exists($waterfall_index)){
            if($params['page'] == 1){
                $last_info = $this->redis->zRevRange($waterfall_index,0,0,true);
                $last_time_arr = array_values($last_info);
                $last_time = (int)$last_time_arr[0];
                if(!empty($last_time)){
                    $params['prev_time'] = $last_time;
                    Task::deliver('WaterFolls','waterFollsGeneral',[$params, $waterfall_index], Task::TYPE_ASYNC);
                }
            }
        }

        $limit = ($params['page'] -1) * $params['psize'];
        $offset = $params['page'] * $params['psize'] - 1;
        $product_list = [];
        $last_waterfall_count = $this->redis->zRevRange($waterfall_index,$limit,$offset,true);
        if(count($last_waterfall_count) == 0){
            $last_info = $this->redis->zRange($waterfall_index,0,0,true);
            $last_time_arr = array_values($last_info);
            $prev_time = (int)$last_time_arr[1];
            $prev_date = date('Y-m-d',$prev_time);
            $flx_count = $params['psize'];
        }else{
            $flx_count = $params['psize'] - count($last_waterfall_count);
            $prev_time = end($last_waterfall_count);
            $prev_date = date('Y-m-d',$prev_time);
        }

        $i = 1;
        while ($flx_count > 0){
            $params['prev_time'] = strtotime("-{$i} day",strtotime($prev_date));
            $params['end_time'] = strtotime($prev_date);
            $params['limit'] = $flx_count;
            Task::deliver('WaterFolls','waterFollsGeneral',[$params, $waterfall_index], Task::TYPE_ASYNC);
            $last_waterfall_count = $this->redis->zRevRange($waterfall_index,$limit,$offset,true);
            $flx_count = $params['psize'] - count($last_waterfall_count) / 2;
            $i++;
        }
        $waterfall_list = $last_waterfall_count;
        if(!empty($waterfall_list)){
            foreach ($waterfall_list as $wk => $wv) {
                if(!empty($wk)){
                    $wtDetail = explode('#',$wk);
                    $product_list[] = (int)$wtDetail[1];
                }
            }
        }

        return $product_list;
    }

    /**
     * 生成瀑布流数据
     * @param $waterfall_index
     * @param $params
     * @throws \Swoft\Db\Exception\DbException
     */
    public function general_waterfolls_data($waterfall_index,$params)
    {
        if(isset($params['end_time']) && $params['end_time'] > 0 && isset($params['prev_time'])){
            $prev_time = $params['prev_time'];
            $last_time = $params['end_time'];
        }else{
            $last_pro = $this->productDao->getLastProductInfo();
            $prev_time = $last_time = $last_pro['addTime'];
            $last_sync_time = $this->redis->get('waterfall_newest_time_' . $params['cycle'] . '_' . $params['display_count']);
            if($prev_time <= $last_sync_time){
                return;
            }
        }

        $start_time = strtotime(date('Y-m-d',$prev_time));
        //获取最新数据
        $last_cache_time = (isset($params['prev_time']) && $params['prev_time'] > $start_time) ? $params['prev_time'] : $start_time;
        //周期判断
        $cycle_num = intval(24/$params['cycle']);
        $cycle_time_list = [];
        $first_start_time = $start_time;
        for ($i = 1; $i <= $params['cycle']; $i++){
            $cycle_now_end_time = $start_time + $i * $cycle_num * 3600;
            $cycle_time_list[$first_start_time] = $cycle_now_end_time;
            $first_start_time = $cycle_now_end_time;
        }
        $user_list = $this->productDao->getProductUserByLastTime($last_cache_time,$last_time);
        if(!empty($user_list)){
            $test_list = $this->userData->getTesters();
            foreach ($user_list as $key => $item) {
                if(in_array($item['userId'],$test_list)){
                    continue;
                }
                //判断所属周期
                $current_level = [];
                foreach ($cycle_time_list as $ck => $cv) {
                    if($ck <= $item['addTime']){
                        $current_level[] = $ck;
                    }
                }
                if(!empty($current_level)){
                    krsort($current_level);
                    $current_user_start_time = current($current_level);
                    $current_user_end_time = $cycle_time_list[$current_user_start_time];
                    $current_list = $this->redis->zRangeByScore($waterfall_index,$current_user_start_time,$current_user_end_time);
                    //周期内产品数判断
                    if(!empty($current_list)){
                        $user_has_queue_count[$item['userId']] = [];
                        foreach ($current_list as $pk => $pv) {
                            $pro_arr = explode('#',$pv);
                            if($pro_arr[0] == $item['userId']){
                                $user_has_queue_count[$item['userId']][] = 1;
                            }
                        }
                        $user_queue_count = array_sum($user_has_queue_count[$item['userId']]);
                        if($user_queue_count < $params['display_count']){
                            $limit_count = $params['display_count'] - $user_queue_count;
                        }
                    }else{
                        $limit_count = $params['display_count'];
                    }
                    //周期内产品补充
                    if(isset($limit_count) && $limit_count > 0){
                        Log::info($item['userId']);
                        $proParams = [
                            ['add_time','>=',$current_user_start_time],
                            ['add_time','<=',$current_user_end_time],
                            'user_id' => $item['userId'],
                            'del_status' => 1
                        ];
                        //符合条件产品数修改
                        $prOption = [
                            'fields' => ['user_id','pro_id','add_time'],
                            'limit' => $limit_count
                        ];
                        $pro_info = $this->productDao->getUserProductListByParams($proParams,$prOption);
                        if(!empty($pro_info)){
                            foreach ($pro_info as $pro) {
                                $this->redis->zAdd($waterfall_index,$pro['addTime'],$pro['userId'] . '#' .$pro['proId']);
                            }
                        }
                    }
                }
                $this->redis->set('waterfall_newest_time_' . $params['cycle'] . '_' . $params['display_count'],$item['addTime']);
            }
        }
    }
    /**
     * @param $pro_id
     * @return \Swoft\Core\ResultInterface
     */
    public function getProductInfo($pro_id)
    {
        return $this->productDao->getProductInfoByPid($pro_id);
    }

    /**
     * @param $match_record
     * @return mixed
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function saveMatchPro($match_record)
    {
        return $this->productDao->saveOfferMatchProRecord($match_record);
    }

    /**
     * @return mixed
     */
    public function getExpireKeyPro()
    {
        return $this->productDao->getExpireSearchRecord();
    }

    /**
     * @param array $params
     * @param array $data
     * @return mixed
     */
    public function updateExpirePro(array $params, array $data)
    {
        return $this->productDao->updateExpireSearchStatus($params,$data);
    }
}