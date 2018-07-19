<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Dao;

use App\Models\Entity\Tag;
use Swoft\Bean\Annotation\Bean;

/**
 * 采购数据对象
 * @Bean()
 * @uses TagDao
 * @author Nihuan
 */
class TagDao
{

    /**
     * @author Nihuan
     * @param array $parent_ids
     * @param array $fields
     * @return mixed
     */
    public function getRankTagList(array $parent_ids, array $fields)
    {
        return Tag::findAll(
            [
                'tag_id' => $parent_ids
            ],
            ['fields' => $fields])->getResult();
    }

}