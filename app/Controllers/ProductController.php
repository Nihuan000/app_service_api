<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Controllers;

use App\Models\Logic\ElasticsearchLogic;
use Swoft\App;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Server\Bean\Annotation\RequestMethod;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;

/**
 * Class ProductController
 * @Controller()
 * @package App\Controllers
 */
class ProductController{

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    /**
     * 自动报价产品字典生成
     * @RequestMapping()
     * @param Request $request
     * @return array
     */
    public function offer_product_tokenize(Request $request): array
    {
        $pro_id = $request->post('product_id');
        $pro_name = $request->post('pro_name');
        $user_id = $request->post('user_id');
        $tokenize_type = $request->post('tokenize_type'); //分析类型 1:新增　2:删除
        if(empty($pro_id) || empty($pro_name) || empty($tokenize_type) || empty($user_id)){
            $code = 0;
            $result = [];
            $msg = '请求参数错误';
        }else{
            $keys = '@OfferProduct_';
            $pro_cache_key = '@OfferProName_';
            /* @var ElasticsearchLogic $elastic_logic */
            if($tokenize_type == 1){
                $cache_list = [];
                $elastic_logic = App::getBean(ElasticsearchLogic::class);
                $tag_list_analyzer = $elastic_logic->tagAnalyzer($pro_name);
                if(isset($tag_list_analyzer) && !empty($tag_list_analyzer)){
                    foreach ($tag_list_analyzer as $analyzer) {
                        $token_key = $keys . md5($analyzer);
                        $this->redis->sAdd($token_key, $pro_id . '#' . $user_id);
                        $cache_list[] = $analyzer;
                    }
                }
                if(!empty($cache_list)){
                    $this->redis->set($pro_cache_key . $pro_id,json_encode($cache_list));
                }
                $code = 1;
                $result = [];
                $msg = '缓存成功';
            }elseif($tokenize_type == 2){
                //取消自动报价，删除缓存数据
                $tokenize_cache = $this->redis->get($pro_cache_key . $pro_id);
                if(!empty($tokenize_cache)){
                    $tokenize_list = json_decode($tokenize_cache,true);
                    foreach ($tokenize_list as $item) {
                        if($this->redis->exists($keys . md5($item))){
                            $this->redis->sRem($keys . md5($item),$pro_id . '#' . $user_id);
                        }
                    }
                    $this->redis->delete($pro_cache_key .$pro_id);
                }
                $code = 1;
                $result = [];
                $msg = '删除成功';
            }
        }
        return compact("code","result","msg");
    }
}
