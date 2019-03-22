<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-25
 * Time: 下午5:41
 */

namespace App\Models\Dao;

use Swoft\Bean\Annotation\Bean;
use App\Models\Entity\Order;
use Swoft\Db\Db;
use Swoft\Db\Query;
use Swoft\Log\Log;

/**
 * 订单数据对象
 * @Bean()
 * @uses OrderDao
 * @author Nihuan
 */
class OrderDao
{

    public function getOrderInfo($order_num,$fields)
    {
        return Order::findOne(['order_num' => $order_num],['fields' => $fields])->getResult();
    }


    /**
     * 根据产品信息获取
     * @author Nihuan
     * @param string $keyword
     * @return array
     */
    public function getOrderWithKeyword(string $keyword)
    {

        //TODO 根据关键词搜索订单列表
        return [];
    }

    /**
     * 对公转账记录获取
     * @param string $order_num
     * @return mixed
     */
    public function getPublicTransferInfo(string $order_num)
    {
        return Query::table('sb_order_public_transfer')->where('order_num',$order_num)->where('status',50)->get()->getResult();
    }

    /**
     * 用户钱包余额
     * @param int $user_id
     * @return mixed
     */
    public function getUserWalletBalance(int $user_id)
    {
        $userBalance = Query::table('sb_order_wallet')->where('user_id',$user_id)->limit(1)->get()->getResult();
        $balance_info = current($userBalance);
        return $balance_info;
    }

    /**
     * 返现到钱包操作
     * @param $order_info
     * @return bool
     * @throws \Swoft\Db\Exception\MysqlException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function returnCashBackToWlt($order_info)
    {
        $return_amount = 50;
        $userBalance = $this->getUserWalletBalance($order_info['buyerId']);
        if(!isset($userBalance['balance'])){
            write_log(3,"用户{$order_info['buyerId']}钱包信息获取失败:" . json_encode($userBalance));
            return false;
        }
        write_log(3,"用户{$order_info['buyerId']}钱包余额:" . $userBalance['balance']);
        //开启事务
        Db::beginTransaction();
        //写入补贴记录
        $recharge['user_id'] = $order_info['buyerId'];
        $recharge['money'] = $return_amount;
        $recharge['add_time'] = time();
        $recharge['remark'] = '对公转账交易满10000元返现' . $return_amount . '元';
        $recharge['order_num'] = $order_info['orderNum'];
        $recharge['type'] = 1;
        $recharge['status'] = 40;
        $recharge['operator_audit_time'] = $recharge['finance_audit_time'] = time();
        $recharge['operator_audit_manager'] = '';
        $recharge['operator_audit_message'] = '自动返现至钱包';
        $recharge['finance_audit_manager'] = '';
        $recharge['finance_audit_message'] = '';
        $rechargeRes = Query::table('sb_order_recharge')->insert($recharge)->getResult();
        //写入交易流水返现记录
        $record['re_type'] = 1;
        $record['order_uid'] = $order_info['buyerId'];
        $record['shop_id'] = $order_info['buyerId'];
        $record['buy_count'] = 1;
        $record['order_num'] = $order_info['orderNum'];
        $record['type'] = 1;
        $record['price'] = $return_amount;
        $record['addtime'] = time();
        $record['status'] = 2;
        $recordRes = Query::table('sb_order_record')->insert($record)->getResult();
        //写入钱包记录
        $wr['user_id'] = $order_info['buyerId'];
        $wr['money'] = $return_amount;
        $wr['order_num'] = $order_info['orderNum'];
        $wr['record_from'] = 10; //补贴
        $wr['record_type'] = 1;
        $wr['record_time'] = time();
        $walletRecRes = Query::table('sb_order_wallet_record')->insert($wr)->getResult();
        //修改钱包金额
        $wu['balance'] = $userBalance['balance'] + $return_amount;
        $wu['update_time'] = time();
        $walletRes = Query::table('sb_order_wallet')->where('user_id',$order_info['buyerId'])->update($wu)->getResult();
        if($recordRes && $walletRecRes && $walletRes && $rechargeRes){
            write_log(3,"用户{$order_info['buyerId']}银行转账10000以上返现{$return_amount}元, 当前余额:" . $wu['balance']);
            Db::commit();
            return true;
        }else{
            $userBalance = $this->getUserWalletBalance($order_info['buyerId']);
            write_log(3,"用户{$order_info['buyerId']}返现{$return_amount}元操作失败, 失败原因: recordRes:{$recordRes},walletRec:{$walletRecRes},walletRes:{$walletRes} 当前余额:" . $userBalance['balance']);
            Db::rollback();
            return false;
        }
    }

    /**
     * 获取所有订单的金额总和
     * @param $user_id
     * @return float
     * @throws \Swoft\Db\Exception\MysqlException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getOrderAllPrice($user_id)
    {
        return Query::table('sb_order')->where('del_status', 1)->where('status', 4)->where('user_id',$user_id)->sum('total_order_price')->getResult();
    }
}