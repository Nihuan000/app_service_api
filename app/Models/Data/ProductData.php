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
use Swoft\Redis\Redis;

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
            foreach ($visit_list as $item) {
                $pro_ids[] = $item['pro_id'];
            }
            $fields = ['type'];
            $product_types = $this->productDao->getProductTypeList($pro_ids,$fields);
            if(!empty($product_types)){
                foreach ($product_types as $product_type) {
                    $type_name = isset($this->pro_cate[$product_type['type']]) ? $this->pro_cate[$product_type['type']] : '';
                    if(!empty($type_name)){
                        $product_tag[$type_name][] = 1;
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
        if($this->redis->exists($waterfall_index)){
            $last_info = $this->redis->zRevRange($waterfall_index,0,0,true);
            $last_time_arr = array_values($last_info);
            $last_time = (int)$last_time_arr[1];
            if(!empty($last_time)){
                $params['prev_time'] = $last_time;
                $this->general_waterfolls_data($waterfall_index,$params);
            }
        }else{
            $this->general_waterfolls_data($waterfall_index,$params);
        }

        $limit = ($params['page'] -1) * $params['psize'];
        $offset = $params['page'] * $params['psize'] - 1;
        $product_list = [];
        $last_waterfall_count = $this->redis->zRevRange($waterfall_index,$limit,$offset,true);
        if(!empty($last_waterfall_count)){
            $flx_count = $params['psize'] - count($last_waterfall_count) / 2;
            $i = 1;
            while ($flx_count > 0){
                $prev_time = end($last_waterfall_count);
                $prev_date = date('Y-m-d',$prev_time);
                $params['prev_time'] = strtotime("-{$i} day",strtotime($prev_date));
                $params['end_time'] = strtotime($prev_date);
                $params['limit'] = $flx_count;
                $this->general_waterfolls_data($waterfall_index,$params);
                $last_waterfall_count = $this->redis->zRevRange($waterfall_index,$limit,$offset,true);
                $flx_count = $params['psize'] - count($last_waterfall_count) / 2;
                $i++;
            }
            $waterfall_list = $last_waterfall_count;
            if(!empty($waterfall_list)){
                foreach ($waterfall_list as $wk => $wv) {
                    if($wk % 2 == 0){
                        $wtDetail = explode('#',$wv);
                        $product_list[] = (int)$wtDetail[1];
                    }
                }
            }
        }

        return $product_list;
    }

    /**
     * 生成瀑布流数据
     * @param $waterfall_index
     * @param $params
     */
    protected function general_waterfolls_data($waterfall_index,$params)
    {
        if(isset($params['end_time']) && $params['end_time'] > 0 && isset($params['prev_time'])){
            $prev_time = $params['prev_time'];
            $last_time = $params['end_time'];
        }else{
            $last_pro = $this->productDao->getLastProductInfo();
            $prev_time = $last_time = $last_pro['addTime'];
        }

        $start_time = strtotime(date('Y-m-d',$prev_time));
        //获取最新数据
        $last_cache_time = isset($params['prev_time']) ? $params['prev_time'] : $start_time;
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
            foreach ($user_list as $key => $item) {
                //判断所属周期
                $current_level = [];
                foreach ($cycle_time_list as $ck => $cv) {
                    if($ck <= $item['addTime']){
                        $current_level[] = $ck;
                    }
                }
                krsort($current_level);
                $current_user_start_time = current($current_level);
                $current_user_end_time = $cycle_time_list[$current_user_start_time];
                $current_list = $this->redis->zRangeByScore($waterfall_index,$current_user_start_time,$current_user_end_time);
                //周期内产品数判断
                if(!empty($current_list)){
                    $user_has_queue_count[$item['userId']] = 0;
                    foreach ($current_list as $pk => $pv) {
                        $pro_arr = explode('#',$pv);
                        if($pro_arr[0] == $item['userId']){
                            $user_has_queue_count[$item['userId']] += 1;
                        }
                    }
                    if($user_has_queue_count[$item['userId']] < $params['display_count']){
                        $limit_count = $params['display_count'] - $user_has_queue_count[$item['userId']];
                    }
                }else{
                    $limit_count = $params['display_count'];
                }

                //周期内产品补充
                if(isset($limit_count) && $limit_count > 0){
                    $proParams = [
                        ['add_time','>=',$current_user_start_time],
                        ['add_time','<=',$current_user_end_time],
                        'user_id' => $item['userId'],
                        'del_status' => 1
                    ];
                    $prOption = [
                        'orderby' => ['pro_id' => 'asc'],
                        'limit' => $params['display_count']
                    ];
                    $pro_info = $this->productDao->getUserProductListByParams($proParams,$prOption);
                    if(!empty($pro_info)){
                        foreach ($pro_info as $pro) {
                            $this->redis->zAdd($waterfall_index,$pro['addTime'],$pro['userId'] . '#' .$pro['proId']);
                        }
                    }
                }
            }
        }
    }
}