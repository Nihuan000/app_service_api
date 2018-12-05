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
        return Db::query("select pro_id from sb_product_records where user_id = {$user_id} and r_time >= {$last_time}")->getResult();
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
     * @return mixed
     */
    public function getProductUserByLastTime($add_time)
    {
        return Product::findAll([['add_time','>', $add_time],'del_status' => 1],['groupBy' => 'user_id','orderBy' => ['add_time' => 'ASC'],'fields' => ['user_id','add_time','pro_id']])->getResult();
    }
}