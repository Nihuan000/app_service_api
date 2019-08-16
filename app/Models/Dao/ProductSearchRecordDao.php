<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Dao;

use App\Models\Entity\ProductSearchLog;
use Swoft\Bean\Annotation\Bean;

/**
 * 产品搜索记录数据对象
 * @Bean()
 * @uses ProductSearchRecordDao
 * @author Nihuan
 */
class ProductSearchRecordDao
{
    /**
     * 产品搜索记录获取
     * @param $params
     * @param $fields
     * @return mixed
     */
    public function getRecordByParams($params,$fields)
    {
        return ProductSearchLog::findAll($params,['fields' => $fields])->getResult();
    }
}
