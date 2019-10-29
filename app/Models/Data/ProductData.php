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
use Swoft\Core\ResultInterface;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
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
     * @throws DbException
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
     * 首页瀑布流数据缓存获取
     * @param array $params
     * @return array
     */
    public function getIndexWaterfalls(array $params)
    {
        $waterfall_index = 'index_water_falls_list_' . $params['cycle'] . '_' . $params['display_count'];
        $cache_key = 'water_fall_index';
        if($this->redis->exists($waterfall_index)){
            if($params['page'] == 1){
                $last_info = $this->redis->zRevRange($waterfall_index,0,0,true);
                $last_time_arr = array_values($last_info);
                $last_time = (int)$last_time_arr[0];
                if(!empty($last_time)){
                    $params['prev_time'] = $last_time;
                    if(!$this->redis->exists($cache_key)){
                        $cache_data = [
                            'index' => $waterfall_index,
                            'params' => json_encode($params)
                        ];
                        $this->redis->hMSet('water_fall_index',$cache_data);
                    }
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
            $prev_time = (int)$last_time_arr[0];
            $prev_date = date('Y-m-d',$prev_time);
            $flx_count = $params['psize'];
        }else{
            $flx_count = $params['psize'] - count($last_waterfall_count);
            $prev_time = end($last_waterfall_count);
            $prev_date = date('Y-m-d',$prev_time);
        }

        $i = 1;
        while ($flx_count > 0){
            $end_day = 0;
            $params['prev_time'] = strtotime("-{$i} day",strtotime($prev_date));
            if($i > 1){
                $end_day = $i - 1;
            }
            $params['end_time'] = strtotime("-{$end_day} day",$prev_date);
            $params['limit'] = $flx_count;
            if(!$this->redis->exists($cache_key)){
                $cache_data = [
                    'index' => $waterfall_index,
                    'params' => json_encode($params)
                ];
                $this->redis->hMSet('water_fall_index',$cache_data);
            }
            $last_waterfall_count = $this->redis->zRevRange($waterfall_index,$limit,$offset,true);
            $flx_count = $params['psize'] - count($last_waterfall_count);
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
     * @throws DbException
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

                    //周期内产品补充
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
							'orderby' => ['add_time' => 'DESC'],
                            'limit' => $params['display_count']
                        ];
                        $pro_info = $this->productDao->getUserProductListByParams($proParams,$prOption);
                        $wait_del_count[$item['userId']] = 0;
                        if(!empty($pro_info)){
                            foreach ($pro_info as $pro) {
                                $cache_value = $pro['userId'] . '#' .$pro['proId'];
                                if($this->redis->zScore($waterfall_index,$cache_value) > 0){
                                    continue;
                                }else{
                                    $wait_del_count[$item['userId']] += 1;
                                    $this->redis->zAdd($waterfall_index,$pro['addTime'],$pro['userId'] . '#' .$pro['proId']);
                                }
                            }
                        }


                    //周期内历史产品数判断并删除
                    if(!empty($current_list)){
                        $user_has_queue_count[$item['userId']] = 0;
                        foreach ($current_list as $pk => $pv) {
                            $pro_arr = explode('#',$pv);
                            if($pro_arr[0] == $item['userId'] && $wait_del_count[$item['userId']] > 0){
                                $this->redis->zRem($waterfall_index,$pv);
                                $wait_del_count[$item['userId']] --;
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
     * @return ResultInterface
     */
    public function getProductInfo($pro_id)
    {
        return $this->productDao->getProductInfoByPid($pro_id);
    }

    /**
     * @param $match_record
     * @return mixed
     * @throws MysqlException
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

    /**
     * 获取搜索产品列表
     * @author yang
     * @param $params
     * @param $fields
     * @return array
     */
    public function getProductSearchLogList(array $params,array $fields)
    {
        return $this->productDao->getProductSearchLogList($params, $fields);
    }

    /**
     * 获取浏览产品信息列表
     * @author yang
     * @param $params
     * @param $fields
     * @return array
     */
    public function getProductRecordsList(array $params,array $fields)
    {
        return $this->productDao->getProductRecordsList($params, $fields);
    }

    /**
     * 获取以产品id为key的数组
     * @param array $params
     * @return array
     * @author yang
     */
    public function getUserProductNames(array $params)
    {
        $names = [];
        $result = $this->productDao->getUserProductListByParams($params,['field'=>['pro_id','name']]);
        if (!empty($result)){
            $names = [];
            foreach ($result as $it) {
                $names[$it['proId']] = $it['name'];
            }
        }
        return $names;
    }

    /**
     * 我的热门产品
     * @param $user_id
     * @return mixed
     */
    public function getUserPopularProduct($user_id)
    {
        return $this->productDao->getPopularProduct($user_id);
    }

    /**
     * 产品记录添加
     * @param array $data
     * @return bool
     */
    public function setProductRecordLog(array $data)
    {
        //记录
        $record = $this->productDao->setVisitProLog($data);
        if($record == 0){
            return true;
        }
        //点击量更新
        $clicks = $this->productDao->updateProClickById($data['pro_id']);
        if($record && $clicks){
            return true;
        }
        return false;
    }
}
