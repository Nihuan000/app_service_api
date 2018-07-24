<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Dao;

use App\Models\Entity\Buy;
use Swoft\Bean\Annotation\Bean;

/**
 * 采购数据对象
 * @Bean()
 * @uses BuyDao
 * @author Nihuan
 */
class BuyDao
{
    /**
     * 主键查询一条数据
     * @author Nihuan
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return Buy::findById($id)->getResult();
    }
}