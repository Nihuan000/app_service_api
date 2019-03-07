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

    /**
     * 获取标签父id
     * @param $keyword
     * @param $fields
     * @return mixed
     */
    public function getTagInfo($keyword,$fields)
    {
        return Tag::findAll([
            ['name','like',"%{$keyword}%"],
            'parent_id' => 0,
            ['top_id','>',0]
        ],
        [
            ['fields' => $fields]
        ])->getResult();
    }

    /**
     * 获取标签列表
     * @param $tag_ids
     * @return mixed
     */
    public function getTagList($tag_ids)
    {
        return Tag::findAll([['tag_id','IN',$tag_ids]])->getResult();
    }

    /**
     * 根据类获取标签列表
     * @param $cate_id
     * @return mixed
     */
    public function getTagByCate($cate_id)
    {
        return Tag::findAll(['cate_id' => $cate_id],['fields' => ['name']])->getResult();
    }

}