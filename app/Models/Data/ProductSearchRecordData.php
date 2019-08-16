<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Data;

use App\Models\Dao\ProductSearchRecordDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 * 产品搜索数据类
 * @Bean()
 * @uses      ProductSearchRecordData
 * @author    Nihuan
 */
class ProductSearchRecordData
{

    /**
     * @Inject()
     * @var ProductSearchRecordDao
     */
    private $proSearchRec;

    /**
     * 记录列表获取
     * @param $params
     * @param $fields
     * @return mixed
     */
    public function getRecordList($params,$fields)
    {
        return $this->proSearchRec->getRecordByParams($params,$fields);
    }
}
