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
     * @param array $black_ids
     * @return mixed
     */
    public function getRelationTagList(array $buy_ids, array $fields, $black_ids = [])
    {
        return BuyRelationTag::findAll(
            [
                'buy_id' => $buy_ids,
                'cate_id' => 1,
                ['top_id','>',0],
                ['tag_id','NOT IN', $black_ids]
            ],
            ['fields' => $fields]
        )->getResult();
    }
}