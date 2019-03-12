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

    /**
     * @Inject()
     * @var ProductData
     */
    private $proData;

    /**
     * @param $buy_id
     * @param array $buy_tags
     * @param array $img_list
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function match_product_tokenize($buy_id, $buy_tags = [], $img_list = [])
    {
        $match_list = [];
        $match_product_list = [];
        $match_pro_shop = [];
        $keys = '@OfferProduct_';
        $index = '@OfferQueue_';
        $date = date('Y-m-d');
        if(!empty($buy_tags)){
            $record['buy_id'] = $buy_id;
            //根据分词与后台标签查询匹配报价产品缓存
            $record['buy_tags'] = implode(',',$buy_tags);
            foreach ($buy_tags as $buy_tag) {
                if($this->redis->exists($keys . md5($buy_tag))){
                    $product_list = $this->redis->sMembers($keys . md5($buy_tag));
                    if(!empty($product_list)){
                        $match_product_record = [];
                        foreach ($product_list as $pro) {
                            $match_pro = explode('#',$pro);
                            $match_list[$match_pro[1]][$match_pro[0]][] = 10;
                            $match_product_list[] = $match_pro[0];
                            $match_pro_shop[$match_pro[0]] = $match_pro[1];
                            $match_product_record[$buy_tag][] = $match_pro[0];
                        }
                    }
                }
            }

            $pro_img_list = [];
            $search_img_product = [];
            $record['buy_img'] = !empty($img_list) ? implode(',',$img_list) : '';
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
                                $score = sprintf('%.2f',$result['score']);
                                if($score >= 0.9){
                                    $pro_img_list[] = [
                                        'pro_id' => $pro_id,
                                        'score' => $score,
                                        'url' => $result['url']
                                    ];
                                    if(!isset($search_match[$pro_id])){
                                        $search_match[$pro_id] = $result['url'];
                                    }
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
                        if(isset($search_match[$match])){
                            $search_img_product[] = [
                                'pro_id' => $match,
                                'url' => $search_match[$match]
                            ];
                        }
                    }

                    //取得分最高产品写入报价队列
//                    if(!empty($last_match_product)){
//                        foreach ($last_match_product as $key => $last_match) {
//                            arsort($last_match);
//                            $auto_offer_pro_id = current(array_keys($last_match));
//                            $queue_content = $buy_id . '#' . $key . '#' . $auto_offer_pro_id;
//                            $this->redis->rPush($index . $date,$queue_content);
//                        }
//                    }
                }
            }

            if(!empty($match_product_record)){
                $match_record_tag = [];
                $product_record_ids = [];
                foreach ($match_product_record as $key => $item) {
                    $match_record_tag[] = $key . '(' . count($item) . ')';
                    $product_record_ids += $item;
                }
                $record['product_id'] = implode(',',array_unique($product_record_ids));
                $record['product_tags'] = implode(',',$match_record_tag);
            }

            if(!empty($search_img_product)){
                $record['search_img_product'] = json_encode($search_img_product);
            }

            if(!empty($pro_img_list)){
                $record_list = array_slice($pro_img_list,0,10);
                $record['pro_img_list'] = json_encode($record_list);
            }
        }

        if(!empty($record)){
            $record['add_time'] = time();
            $this->proData->saveMatchPro($record);
        }
    }
}