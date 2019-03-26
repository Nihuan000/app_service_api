<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Dao;

use App\Models\Entity\Product;
use App\Models\Entity\Tag;
use Swoft\Bean\Annotation\Bean;
use Swoft\Db\Db;
use Swoft\Db\Query;

/**
 * 产品数据对象
 * @Bean()
 * @uses ProductDao
 * @author Nihuan
 */
class ProductDao
{

    /**
     * 获取用户访问产品记录
     * @param $user_id
     * @param $last_time
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserProductVisitLog($user_id,$last_time)
    {
        return Db::query("select pro_id,r_time from sb_product_records where user_id = {$user_id} and r_time >= {$last_time}")->getResult();
    }

    /**
     * 获取指定产品数据
     * @param array $pro_ids
     * @param array $fields
     * @return \Swoft\Core\ResultInterface
     */
    public function getProductTypeList(array $pro_ids,array $fields)
    {
        return Product::findAll(
            [
                'pro_id' => $pro_ids
            ],
            [
                'fields' => $fields
            ]
        )->getResult();
    }

    /**
     * 获取最新一条产品数据
     * @param $add_time
     * @return \Swoft\Core\ResultInterface
     */
    public function getProductByLastTime($add_time)
    {
        return Product::findOne([['add_time','>', $add_time],'del_status' => 1])->getResult();
    }

    /**
     * 获取用户列表
     * @param $add_time
     * @param $end_time
     * @return mixed
     */
    public function getProductUserByLastTime($add_time,$end_time)
    {
        return Product::findAll([['add_time','>=', $add_time],['add_time','<', $end_time],'del_status' => 1],['groupBy' => 'user_id','orderBy' => ['add_time' => 'ASC'],'fields' => ['user_id','add_time','pro_id']])->getResult();
    }

    /**
     * 获取最后一条数据
     * @return mixed
     */
    public function getLastProductInfo()
    {
        return Product::findOne(['del_status' => 1],['orderby' => ['add_time' => 'desc']])->getResult();
    }

    /**
     * 获取用户产品列表
     * @param array $params
     * @param array $options
     * @return mixed
     */
    public function getUserProductListByParams(array $params, array $options)
    {
        return Product::findAll($params,$options)->getResult();
    }

    /**
     * 获取产品个数
     * @param $add_time
     * @param $limit
     * @return mixed
     */
    public function getProductUserByPrevTime($add_time,$limit)
    {
        return Product::findAll([['add_time','<', $add_time],'del_status' => 1],['groupBy' => 'user_id','orderBy' => ['add_time' => 'DESC'],'fields' => ['user_id','add_time','pro_id'],'limit' => $limit])->getResult();
    }

    /**
     * 获取符合条件的产品数
     * @param array $params
     * @return mixed
     */
    public function getProductCountByParams(array $params)
    {
        return Product::count('*',$params)->getResult();
    }

    /**
     * 获取产品信息
     * @param int $pid
     * @return \Swoft\Core\ResultInterface
     */
    public function getProductInfoByPid(int $pid)
    {
        return Product::findOne(['pro_id' => $pid])->getResult();
    }

    /**
     * 采购自动报价匹配记录
     * @param array $record
     * @return mixed
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function saveOfferMatchProRecord(array $record)
    {
        return Query::table('sb_auto_offer_match_record')->insert($record)->getResult();
    }

    /**
     * 已过期推广获取
     * @return mixed
     */
    public function getExpireSearchRecord()
    {
        $now_time = time();
        return Query::table('sb_operate_search_keyword')->where('parent_id',0,'>')->where('status',0)->where('expire_time',$now_time,'>')->get(['id','value','parent_id'])->getResult();
    }

    /**
     * 已过期状态修改
     * @param $params
     * @param $data
     * @return mixed
     */
    public function updateExpireSearchStatus($params, $data)
    {
        return Query::table('sb_operate_search_keyword')->whereIn('id',$params['ids'])->where('status',$params['status'])->update($data)->getResult();
    }
}