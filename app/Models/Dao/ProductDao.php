<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Dao;

use App\Models\Entity\Product;
use App\Models\Entity\ProductSearchLog;
use App\Models\Entity\ProductRecords;
use App\Models\Entity\Tag;
use Swoft\Bean\Annotation\Bean;
use Swoft\Core\ResultInterface;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
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
     * @throws DbException
     */
    public function getUserProductVisitLog($user_id,$last_time)
    {
        return Db::query("select pro_id,r_time from sb_product_records where user_id = {$user_id} and r_time >= {$last_time}")->getResult();
    }

    /**
     * 获取指定产品数据
     * @param array $pro_ids
     * @param array $fields
     * @return ResultInterface
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
     * @return ResultInterface
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
        return Product::findAll([['add_time','>', $add_time],['add_time','<=', $end_time],'del_status' => 1],['groupBy' => 'user_id','orderBy' => ['add_time' => 'ASC'],'fields' => ['user_id','add_time','pro_id']])->getResult();
    }

    /**
     * 获取最后一条数据
     * @return mixed
     */
    public function getLastProductInfo()
    {
        return Product::findOne(['del_status' => 1],['orderby' => ['add_time' => 'desc'],'fields' => ['pro_id','add_time','user_id']])->getResult();
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
     * @return ResultInterface
     */
    public function getProductInfoByPid(int $pid)
    {
        return Product::findOne(['pro_id' => $pid])->getResult();
    }

    /**
     * 采购自动报价匹配记录
     * @param array $record
     * @return mixed
     * @throws MysqlException
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
        return Query::table('sb_operate_search_keyword')->where('parent_id',0,'>')->where('status',0)->where('expire_time',$now_time,'<')->where('expire_time',0,'>')->get(['id','value','parent_id'])->getResult();
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

    /**
     * 获取信息
     * @param array $params
     * @param array $fields
     * @return array
     * @author yang
     */
    public function getProductSearchLogList(array $params,array $fields)
    {
        return ProductSearchLog::findAll($params,['fields' => $fields])->getResult();
    }

    /**
     * 获取信息
     * @param array $params
     * @param array $fields
     * @return array
     * @author yang
     */
    public function getProductRecordsList(array $params,array $fields)
    {
        return ProductRecords::findAll($params,['fields' => $fields])->getResult();
    }

    /**
     * 用户热门产品获取
     * @param $user_id
     * @return mixed
     */
    public function getPopularProduct($user_id)
    {
        return Product::findAll(['user_id' => $user_id, 'del_status' => 1, 'is_up' => 1],['fields' => ['pro_id','name'],'orderby' => ['clicks' => 'desc'], 'limit' => 10])->getResult();
    }

    /**
     * 添加访问记录
     * @param array $data
     * @return bool|ResultInterface
     */
    public function setVisitProLog(array $data)
    {
        $record = new ProductRecords();
        $exists = $record::findOne(['user_id' => $data['user_id'], 'r_time' => $data['r_time'],'pro_id' => $data['pro_id']])->getResult();
        if($exists){
            return 0;
        }
        $record->setUserId($data['user_id']);
        $record->setProId($data['pro_id']);
        $record->setRTime($data['r_time']);
        $record->setFromType($data['from_type']);
        $record->setScene($data['scene']);
        $record->setBusiness($data['business']);
        $record->setIsFilter($data['is_filter']);
        $record->setRequestId($data['request_id']);
        return $record->save()->getResult();
    }

    /**
     * 修改产品信息
     * @param int $id
     * @return mixed
     */
    public function updateProClickById(int $id)
    {
        $proInfo = Product::findById($id)->getResult();
        if(!empty($proInfo)){
            $data = [
                'clicks' => $proInfo['clicks'] + 1,
                'alter_time' => time()
            ];
            return Product::updateOne($data,['pro_id' => $id])->getResult();
        }
        return false;
    }

    /**
     * 获取产品图片列表
     * @param int $id
     * @return mixed
     */
    public function productImgList(int $id)
    {
        return Query::table('product_img')->where('pro_id',$id)->get(['pimg_id','img','img_width'])->getResult();
    }

    /**
     * 修改产品图片信息
     * @param int $id
     * @param $data
     * @return mixed
     */
    public function updateProImg(int $id, $data)
    {
        return Query::table('product_img')->where('pimg_id',$id)->update($data)->getResult();
    }

    /**
     * 图片列表获取
     * @param int $psize
     * @return mixed
     */
    public function noSizeImgList($psize = 20)
    {
        return Query::table('product_img')->limit($psize)->get(['pimg_id','img','img_width'])->getResult();
    }
}
