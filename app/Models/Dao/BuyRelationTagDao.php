<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 下午2:35
 */

namespace App\Models\Dao;

use App\Models\Entity\BuyRelationTag;
use Swoft\Bean\Annotation\Bean;

/**
 * 采购标签数据对象
 * @Bean()
 * @uses OrderDao
 * @author Nihuan
 */
class BuyRelationTagDao
{
    /**
     * @author Nihuan
     * @param array $buy_ids
     * @param array $fields
     * @return mixed
     */
    public function getRelationTagList(array $buy_ids, array $fields)
    {
        return BuyRelationTag::findAll(
            [
                'buy_id' => $buy_ids,
                'cate_id' => 1,
                ['top_id','>',0]
            ],
            ['fields' => $fields]
        )->getResult();
    }
}