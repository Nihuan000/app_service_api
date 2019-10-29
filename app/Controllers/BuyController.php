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

use App\Models\Data\BuyData;
use App\Models\Data\UserData;
use App\Models\Logic\ElasticsearchLogic;
use App\Models\Logic\ProductLogic;
use QL\QueryList;
use Swoft\App;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Task\Task;

/**
 * Class BuyControllerController
 * @Controller()
 * @package App\Controllers
 */
class BuyController{

    /**
     * @Inject()
     * @var BuyData
     */
    protected $buyData;

    /**
     * @Inject()
     * @var UserData
     */
    protected $userData;

    /**
     * 实商匹配采购自动报价
     * @RequestMapping()
     * @param Request $request
     * @return array
     * @throws \Swoft\Task\Exception\TaskException
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
            write_log(2,$buy_id . '=>' . $buy_remark . ' => ' . $tag_list . ' => ' . $buy_img_list);
            Task::deliver('Offer','Product_Auto_offer',[$buy_id, $buy_remark,$tag_list,$buy_img_list], Task::TYPE_ASYNC);
            $code = 0;
            $result = [];
            $msg = '成功';
        }
        return compact("code","result","msg");
    }

    /**
     * 采购信息远程拉取
     * @RequestMapping()
     * @param Request $request
     * @return array
     */
    public function tnc_buy_pull(Request $request): array
    {
        $tnc_url = $request->post('tnc_url');
        if(empty($tnc_url)){
            $code = 0;
            $result = [];
            $msg = '请求参数错误';
        }else{
            $buy = [
                'amount' => 0,
                'cover' => '',
                'remark' => '',
                'pic_list' => [],
            ];
            $headers = [
                'Referer' => 'https://www.tnc.com.cn/buyoffer/',
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:70.0) Gecko/20100101 Firefox/70.0',
                'Host' => 'www.tnc.com.cn',
            ];
            $rules = [
                //采购数量
                'amount' => ['.cont-info>p:eq(0)','text'],
                //采购封面
                'cover' => ['div.pic-manager div.gallery a.jqzoom','href'],
                //采购详情
                'remark' => ['div.detail-info>div.info-bd','text']
            ];
            $pic_rules = [
                //采购图片
                'pic_list' => ['div.pic-manager div.clearfix ul#thumblist li>a', 'rel'],
            ];
            $query = new QueryList();
            $queryInfo = $query->get($tnc_url,"",$headers)->encoding('UTF-8');
            $data = $queryInfo->rules($rules)->queryData();
            if(!empty($data)){
                $buy_info = current($data);
                $amount = str_replace('采购数量：','',$buy_info['amount']);
                $buy['amount'] = $amount > 0 ? (int)$amount : 1000;
                $buy['remark'] = trim(str_replace([PHP_EOL,' ','\r'],'',$buy_info['remark']));
                $buy['cover'] = trim($buy_info['cover']);
            }
            $pic = $queryInfo->rules($pic_rules)->queryData();
            $pic_list = [];
            if(!empty($pic)){
                foreach ($pic as $item) {
                    $tmp_string = str_replace(['{','}'],'',$item['pic_list']);
                    $img_attr = explode(',',$tmp_string);
                    if(is_array($img_attr)){
                        $pic_arr = explode(': ',end($img_attr));
                        if(count($pic_arr) > 0){
                            $img = isset($pic_arr[1]) ? str_replace("'",'',$pic_arr[1]) : '';
                            $pic_list[] = $img;
                        }
                    }
                }
            }

            $buy['pic_list'] = $pic_list;
            $result['data'] = $buy;
        }
        return compact("code","result","msg");
    }
}
