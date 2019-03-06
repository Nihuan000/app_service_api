<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-20
 * Time: 上午11:07
 */

namespace App\Controllers;

use App\Models\Logic\OrderLogic;
use App\Models\Logic\UserLogic;
use Swoft\App;
use Swoft\Db\Db;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Message\Server\Request;


/**
 * @Controller()
 * Class OrderController
 */
class OrderController
{

    /**
     * 关键词搜索获取订单编号
     * @RequestMapping()
     * @author Nihuan
     * @param Request $request
     * @throws \Swoft\Db\Exception\DbException
     * @return array
     */
	public function order_search(Request $request)
    {
        //关键词
        $keyword = $request->post('keyword');
        //用户id
        $user_id = $request->post('user_id');
        //用户类型 1:买家 2:卖家
        $user_type = $request->post('user_type');
        if(!in_array($user_type,[1,2])){
            $code = 0;
            $result = [];
            $msg = '请求参数错误: user_type';
        }elseif($user_id == false){
            $code = 0;
            $result = [];
            $msg = '请求参数错误: user_id';
        }elseif($keyword == false){
            $code = 0;
            $result = [];
            $msg = '请求参数错误: keyword';
        }else{
            if($user_type == 1){
                $order_num = Db::query("SELECT t.order_num FROM sb_order t LEFT JOIN sb_order_product_relation AS pr ON pr.order_num = t.order_num RIGHT JOIN sb_order_product AS p ON p.order_num = pr.order_sub_num WHERE t.del_status = 1 AND t.seller_name like '%{$keyword}%' OR p.title like '%{$keyword}%' AND t.buyer_id = ?",[(int)$user_id])->getResult();
				$order_num_list = array_column($order_num,'order_num');
            }else{
               //货号/产品名判断
                $pro_item = Db::query("SELECT group_concat(pro_id) AS pro_ids FROM sb_product WHERE user_id = {$user_id} AND (pro_item = '{$keyword}' OR name LIKE '%{$keyword}%')")->getResult();

                $pro_item_list = $pro_item[0]['pro_ids'];
                $where = "t.buyer_name like '%{$keyword}%' OR t.order_num = '{$keyword}'";
                //订单号/买家名判断
                if(!empty($pro_item_list)){
                    $where .= " OR p.pro_id IN ($pro_item_list)";
                }
                $order_num = Db::query("SELECT t.order_num FROM sb_order t LEFT JOIN sb_order_product_relation AS pr ON pr.order_num = t.order_num RIGHT JOIN sb_order_product AS p ON p.order_num = pr.order_sub_num WHERE t.del_status = 1 AND ({$where}) AND t.seller_id = ?", [(int)$user_id])->getResult();
                $order_num_list = array_column($order_num,'order_num');
            }

            $order_list = empty($order_num_list) ? [] : $order_num_list;
            $code = 200;
            $msg = '订单获取成功';
            $result = ['order_list' => $order_list];
        }
        return compact("code","result","msg");
    }

    /**
     * 订单服务费统计
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function order_service_fee(Request $request)
    {
        $order_num = $request->post('order_num');
        $user_id = $request->post('user_id');
        $take_time = $request->post('take_time');
        $total_amount = $request->post('total_amount');
        if(empty($order_num) || empty($user_id) || empty($take_time) || empty($total_amount)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            //判断订单状态
            /* @var OrderLogic $order_logic */
            $order_logic = App::getBean(OrderLogic::class);
            $fields = ['status','order_num'];
            $checkOrder = $order_logic->get_order_info($order_num,$fields);
            if($checkOrder['status'] == 4){
                /* @var UserLogic $user_logic */
                $user_logic = App::getBean(UserLogic::class);
                $plusRes = $user_logic->strengthUserOrderTotal($user_id,$order_num,$total_amount,$take_time);
                if($plusRes){
                    $code = 200;
                    $result = [];
                    $msg = '更新成功';
                }
            }else{
                $code = 0;
                $result = [];
                $msg = '订单状态不正确';
            }
        }

        return compact('code','result','msg');
    }

    /**
     * 对公转账满额返现
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function order_cash_back(Request $request)
    {
        $order_num = $request->post('order_num');
        if(empty($order_num)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            //判断订单状态
            /* @var OrderLogic $order_logic */
            $order_logic = App::getBean(OrderLogic::class);
            $fields = ['status','order_num','pay_type','total_order_price','real_get','coupon_price','buyer_id'];
            $checkOrder = $order_logic->get_order_info($order_num,$fields);
            if($checkOrder['status'] != 4){
                $code = 0;
                $result = [];
                $msg = '订单状态不正确';
            }elseif($checkOrder['realGet'] + $checkOrder['couponPrice'] < 10000){
                $code = 0;
                $result = [];
                $msg = '订单金额不符合条件';
            }else{
                $order_logic->return_cash_action($checkOrder);
                $code = 1;
                $result = [];
                $msg = '操作成功';
            }
        }

        return compact('code','result','msg');
    }
}
