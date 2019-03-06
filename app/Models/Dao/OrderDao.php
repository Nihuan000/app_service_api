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
     * 返现到钱包操作
     * @param array $order_info
     * @return bool
     * @throws \Swoft\Db\Exception\MysqlException
     * @throws \Swoft\Db\Exception\DbException
     */
    public function returnCashBackToWlt(array $order_info)
    {
        $return_amount = 50;
        $userBalance = Query::table('sb_order_wallet')->where('user_id',$order_info['buyerId'])->get(['balance'])->getResult();
        write_log(3,"用户{$order_info['buyerId']}钱包余额:" . $userBalance['balance']);
        //开启事务
        Db::beginTransaction();
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
        if($recordRes && $walletRecRes && $walletRes){
            write_log(3,"用户{$order_info['buyerId']}银行转账10000以上返现{$return_amount}元, 当前余额:" . $wu['balance']);
            Db::commit();
            return true;
        }else{
            $userBalance = Query::table('sb_order_wallet')->where('user_id',$order_info['buyerId'])->get(['balance'])->getResult();
            write_log(3,"用户{$order_info['buyerId']}返现{$return_amount}元操作失败, 失败原因: recordRes:{$recordRes},walletRec:{$walletRecRes},walletRes:{$walletRes} 当前余额:" . $userBalance['balance']);
            Db::rollback();
            return false;
        }
    }
}