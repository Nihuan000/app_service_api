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
use App\Models\Data\ProductData;
use App\Models\Data\UserData;
use App\Models\Logic\ElasticsearchLogic;
use App\Models\Logic\UserLogic;
use Swoft\Http\Message\Server\Request;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;
use Swoft\App;
use Swoft\Core\Coroutine;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Log\Log;
use Swoft\View\Bean\Annotation\View;
use Swoft\Contract\Arrayable;
use Swoft\Http\Server\Exception\BadRequestException;
use Swoft\Http\Message\Server\Response;
use ProductAI;

/**
 * Class IndexController
 * @Controller()
 */
class IndexController
{
    /**
     * @Inject()
     * @var BuyData
     */
    private $buyData;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

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
     * @RequestMapping("/")
     * @View(template="index/index")
     * @return array
     */
    public function index(): array
    {
        $name = 'Framework';
        $notes = [
            'New Generation of PHP Framework',
            'Hign Performance, Coroutine and Full Stack'
        ];
        $links = [
        ];
        // 返回一个 array 或 Arrayable 对象，Response 将根据 Request Header 的 Accept 来返回数据，目前支持 View, Json, Raw
        return compact('name', 'notes', 'links');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function offer_test(Request $request)
    {
        $msg_arr = explode('#',$request->post('msg_record'));
        $user_id = (int)$msg_arr[0];
        $buy_id = (int)$msg_arr[1];
        $buyInfo = $this->buyData->getBuyInfo($buy_id);
        $buyer = $this->userData->getUserInfo((int)$buyInfo['userId']);
        $is_send_offer = env('SEND_OFFER_NOTICE');
        $setting_info = $this->userData->getSetting('recommend_deposit_switch');
        Log::info($setting_info);
        $config = \Swoft::getBean('config');
        $sys_msg = $is_send_offer==1 ? $config->get('offerMsg') : $config->get('sysMsg');

        if($is_send_offer == 1){
            ################## 消息展示内容开始 #######################
            $extra = $sys_msg;
            $extra['image'] = !is_null($buyInfo['pic']) ? get_img_url($buyInfo['pic']) : '';
            $extra['type'] = $buyInfo['status'];
            $extra['id'] = $buy_id;
            $extra['buy_id'] = $buy_id;
            $extra['name'] = $buyer['name'];
            $extra['title'] = (string)$buyInfo['remark'];
            $extra['amount'] = $buyInfo['amount'];
            $extra['unit'] = $buyInfo['unit'];
            ################## 消息展示内容结束 #######################

            ################## 消息基本信息开始 #######################
            $extra['msgTitle'] = '收到邀请';
            $extra['msgContent'] = "买家{$buyer['name']}邀请您为他报价！";
            ################## 消息基本信息结束 #######################

            $notice['extra'] = $extra;
            $sendRes = sendInstantMessaging('11', (string)$user_id, json_encode($notice['extra']));
        }else{
            ################## 消息展示内容开始 #######################
            $buy_info['image'] = !is_null($buyInfo['pic']) ? get_img_url($buyInfo['pic']) : '';
            $buy_info['type'] = 1;
            $buy_info['title'] = (string)$buyInfo['remark'];
            $buy_info['id'] = $buy_id;
            $buy_info['price'] = isset($buyInfo['price']) ? $buyInfo['price'] : "";
            $buy_info['amount'] = $buyInfo['amount'];
            $buy_info['unit'] = $buyInfo['unit'];
            $buy_info['url'] = '';
            ################## 消息展示内容结束 #######################

            ################## 消息基本信息开始 #######################
            $extra = $sys_msg;
            $extra['title'] = '收到邀请';
            $extra['msgContent'] = "买家{$buyer['name']}邀请您为他报价！\n查看详情";
            $extra['commendUser'] = [];
            $extra['showData'] = empty($buy_info) ? [] : [$buy_info];
            ################## 消息基本信息结束 #######################

            ################## 消息扩展字段开始 #######################
            $extraData['keyword'] = '#查看详情#';
            $extraData['type'] = 1;
            $extraData['id'] = (int)$buy_id;
            $extraData['url'] = '';
            ################## 消息扩展字段结束 #######################

            $extra['data'] = [$extraData];
            $extra['content'] = "买家{$buyer['name']}邀请您为他报价！\n#查看详情#";
            $notice['extra'] = $extra;
            $sendRes = sendInstantMessaging('1', (string)$user_id, json_encode($notice['extra']));
        }
        $name = '未知结果';
        if($sendRes){
            $name = '发送成功';
        }
        return compact('name');
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function supplier_test(Request $request)
    {
        $user_id = $request->post('user_id');
        if(empty($user_id)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $last_days = date('Y-m-d',strtotime("-50 day"));
            $last_day_time = strtotime($last_days);
            if($last_days > 0){
                $params = [
                    ['last_time','>=', $last_day_time],
                    'user_id' => $user_id
                ];
                /* @var UserLogic $user_logic */
                $user_logic = App::getBean(UserLogic::class);
                $user_logic->supplierDataList($params, $last_day_time, 50);
            }
            $code = 200;
            $result = ['list' => []];
            $msg = '获取成功';
        }
        return compact('code','result','msg');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function malong_test(Request $request)
    {
        $img_url = $request->post('img_url');
        if(empty($img_url)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $search_match = [];
            $product_ai = new ProductAI\API(env('MALONG_ACCESS_ID'),env('MALONG_SECRET_KEY'));
            $response = $product_ai->searchImage('search',env('MALONG_SERVICE_ID'),get_img_url($img_url),[],[],50);
            if($response != false && $response['is_err'] == 0){
                foreach ($response['results'] as $result) {
                    $search_match[] = $result;
                }
            }
            $code = 0;
            $result = ['search_match' => $search_match];
            $msg = '请求成功';
        }

        return compact('code','result','msg');
    }

    /**
     * 刷新自动报价产品缓存
     * @param Request $request
     * @return array
     */
    public function refresh_auto_offer_product(Request $request)
    {
        $pro_ids = $request->post('pro_id');
        if(empty($pro_ids)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $keys = '@OfferProduct_';
            $pro_cache_key = '@OfferProName_';
            $pro_arr = json_decode($pro_ids,true);
            if(!empty($pro_arr)){
                /* @var ElasticsearchLogic $elastic_logic */
                foreach ($pro_arr as $key => $tmp) {
                    $pro_id = $tmp['pro_id'];
                    $user_id = $tmp['user_id'];
                    if($this->redis->exists($pro_cache_key . $pro_id)){
                        //删除缓存数据
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
                    }

                    $proInfo = $this->proData->getProductInfo($pro_id);
                    if(!empty($proInfo)){
                        $cache_list = [];
                        $elastic_logic = App::getBean(ElasticsearchLogic::class);
                        $tag_list_analyzer = $elastic_logic->tagAnalyzer($proInfo['name'] . ','. $proInfo['pro_name']);
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
                    }
                }
            }
            $code = 1;
            $result = [];
            $msg = '缓存成功';
        }
        return compact("code","result","msg");
    }

    /**
     * 更新自动报价产品缓存
     * @author yang
     * @param Request $request
     * @return array
     */
    public function update_auto_offer_product(Request $request)
    {
        $data = $request->post('data');
        $data = json_decode($data,true);
        $keys = '@OfferProduct_';
        $pro_cache_key = '@OfferProName_';
        $cache_keys = 'product_name_tag_dict';
        if (!empty($data)){

            //更新标签列表
            $tag_cache = $this->redis->get($cache_keys);
            $tag_list = json_decode($tag_cache,true);

            foreach ($data as $key => $value) {
                //获取产品信息
                $proInfo = $this->proData->getProductInfo($key);
                if (empty($proInfo)) { continue; }

                //增加标签
                if (!in_array($value,$tag_list)){
                    $tag_list[] = $value;
                }

                //更新标签下产品
                if (empty($proInfo)){
                    $token_key = $keys . md5($value);
                    $this->redis->sAdd($token_key, $key . '#' . $proInfo['user_id']);
                }

                //更新产品标签集合
                $this->redis->set($pro_cache_key . $key,json_encode([$value]));
            }

            if (!empty($tag_list)) {
                $this->redis->set($cache_keys,json_encode($tag_list));
            }

            $code = 1;
            $result = [];
            $msg = '更新'.count($data).'次，成功';
            return compact("code","result","msg");
        }
    }

}
