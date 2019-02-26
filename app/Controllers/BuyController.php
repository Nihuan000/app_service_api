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
use App\Models\Logic\ProductLogic;
use Swoft\App;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;

/**
 * Class BuyControllerController
 * @Controller()
 * @package App\Controllers
 */
class BuyController{
    /**
     * 实商匹配采购自动报价
     * @RequestMapping()
     * @param Request $request
     * @return array
     */
    public function buy_auto_offer(Request $request): array
    {
        $buy_id = $request->post('buy_id');
        $buy_remark = $request->post('remark');
        $tag_list = $request->post('buy_tag');
        $buy_img_list = $request->post('img_list');
        if(empty($buy_id) || (empty($buy_remark) && empty($tag_list))){
            $code = 0;
            $result = [];
            $msg = '请求参数错误';
        }else{
            $tag_list = json_decode($tag_list,true);
            $buy_img_list = json_decode($buy_img_list,true);
            /* @var ElasticsearchLogic $elastic_logic */
            $elastic_logic = App::getBean(ElasticsearchLogic::class);
            $tag_list_analyzer = $elastic_logic->tokenAnalyzer($buy_remark);
            if(isset($tag_list_analyzer['tokens']) && !empty($tag_list_analyzer['tokens'])){
                foreach ($tag_list_analyzer['tokens'] as $analyzer) {
                    $tag_list[] = $analyzer['token'];
                }
            }
            $buy_tag_list = array_unique($tag_list);
            //匹配产品数据
            /* @var ProductLogic $product_logic */
            $product_logic = App::getBean(ProductLogic::class);
            $product_logic->match_product_tokenize($buy_id, $buy_tag_list, $buy_img_list);
            $code = 0;
            $result = [];
            $msg = '成功';
        }
        return compact("code","result","msg");
    }
}
