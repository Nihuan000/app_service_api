<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Data;

use App\Models\Dao\ProductDao;
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

    public function getIndexWaterfalls(array $params)
    {
        $waterfall_index = 'index_water_falls_list_' . $params['cycle'] . '_' . $params['display_count'];
        if($this->redis->exists($waterfall_index)){
            $last_info = $this->redis->zRevRange($waterfall_index,0,1,true);
            $info = current($last_info);
        }else{
            $start_time = strtotime(date('Y-m-d'));
            //周期判断
            $cycle_num = intval($params['cycle'] / 24);
            $cycle_time_list = [];
            $first_start_time = $start_time;
            for ($i = 1; $i <= $cycle_num; $i++){
                $cycle_now_end_time = $start_time + $i * $cycle_num;
                $cycle_time_list[$first_start_time] = $cycle_now_end_time;
                $first_start_time = $cycle_now_end_time;
            }
            $user_list = $this->productDao->getProductUserByLastTime($start_time);
            if(!empty($user_list)){
                foreach ($user_list as $key => $item) {
                    foreach ($cycle_time_list as $ck => $cv) {
//                        if($key <= $current_last){
//                            $current_level[] = $key;
//                        }
                    }
                    krsort($current_level);
                    $user_level = current($current_level);
                }
            }
        }
//        $last_product = $this->productDao->getProductByLastTime($last_time);
        return [];
    }
}