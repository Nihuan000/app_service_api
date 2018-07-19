<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Data;

use App\Models\Dao\TagDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 * 标签数据类
 * @Bean()
 * @uses      TagData
 * @author    Nihuan
 */
class TagData
{

    /**
     * 标签数据对象
     * @Inject()
     * @var TagDao
     */
    private $tagDao;

    /**
     * 根据父级标签获取排序列表
     * @author Nihuan
     * @param $parent_ids
     * @param $fields
     * @return mixed
     */
    public function getRankByParentIds($parent_ids, $fields)
    {
        return $this->tagDao->getRankTagList($parent_ids, $fields);
    }
}