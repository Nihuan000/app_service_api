<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 19-2-25
 * Time: 下午4:41
 */

namespace App\Models\Logic;

use App\Models\Data\ProductData;
use ProductAI;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;

/**
 *
 * @Bean()
 * @uses  ProductLogic
 */
class ProductLogic
{

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    public function match_product_tokenize($buy_id, $buy_tags = [], $img_list = [])
    {
        $match_list = [];
        $match_product_list = [];
        $match_pro_shop = [];
        $keys = '@OfferProduct_';
        $index = '@OfferQueue_';
        $date = date('Y-m-d');
        if(!empty($buy_tags)){
            //根据分词与后台标签查询匹配报价产品缓存
            foreach ($buy_tags as $buy_tag) {
                if($this->redis->exists($keys . md5($buy_tag))){
                    $product_list = $this->redis->sMembers($keys . md5($buy_tag));
                    if(!empty($product_list)){
                        foreach ($product_list as $pro) {
                            $match_pro = explode('#',$pro);
                            $match_list[$match_pro[1]][$match_pro[0]][] = 10;
                            $match_product_list[] = $match_pro[0];
                            $match_pro_shop[$match_pro[0]] = $match_pro[1];
                        }
                    }
                }
            }
            if(!empty($match_product_list)){
                $search_match = [];
                $all_match_product = array_unique($match_product_list);
                //匹配结果通过搜图过滤颜色差异太大产品
                if(!empty($img_list)){
                    $product_ai = new ProductAI\API(env('MALONG_ACCESS_ID'),env('MALONG_SECRET_KEY'));
                    foreach ($img_list as $img) {
                        $response = $product_ai->searchImage('search',env('MALONG_SERVICE_ID'),get_img_url($img),[],[],50);
                        if($response != false && $response['is_err'] == 0){
                            foreach ($response['results'] as $result) {
                                $pro_id = $result['metadata'];
                                if(!isset($search_match[$pro_id])){
                                    $search_match[$pro_id] = $result['url'];
                                }
                            }
                        }
                    }
                    $img_pro_ids = array_keys($search_match);
                    //取标题匹配与图片过滤交集
                    $all_match_product = array_intersect($all_match_product,$img_pro_ids);
                }

                //计算匹配产品总得分
                if(!empty($all_match_product)){
                    $last_match_product = [];
                    foreach ($all_match_product as $match) {
                        if(isset($match_pro_shop[$match])){
                            $match_shop = $match_pro_shop[$match];
                            $last_match_product[$match_shop][$match] = isset($match_list[$match_shop][$match]) ? array_sum($match_list[$match_shop][$match]) : 0;
                        }
                    }

                    //取得分最高产品写入报价队列
                    if(!empty($last_match_product)){
                        foreach ($last_match_product as $key => $last_match) {
                            arsort($last_match);
                            $auto_offer_pro_id = current(array_keys($last_match));
                            $queue_content = $buy_id . '#' . $key . '#' . $auto_offer_pro_id;
                            $this->redis->rPush($index . $date,$queue_content);
                        }
                    }
                }
            }
        }
    }
}