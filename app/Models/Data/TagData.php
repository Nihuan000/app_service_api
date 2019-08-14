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
use Swoft\Db\Exception\MysqlException;

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

    /**
     * 根据关键词获取标签列表
     * @param $keyword
     * @return array
     */
    public function getTopTagByKeyword($keyword)
    {
        $tag_list = [];
        $fields = ['top_id'];
        $top_ids = $this->tagDao->getTagInfo($keyword,$fields);
        if(!empty($top_ids)){
            $tag_ids = [];
            foreach ($top_ids as $key => $item) {
                $tag_ids[] = $item['topId'];
            }
            $tag_name_list = $this->tagDao->getTagList($tag_ids);
            if(!empty($tag_name_list)){
                foreach ($tag_name_list as $item) {
                    $tag_list[] = $item['tagName'];
                }
            }
        }
        return $tag_list;
    }

    /**
     * @param $cate_id
     * @return mixed
     */
    public function getTagListByCate($cate_id)
    {
        return $this->tagDao->getTagByCate($cate_id);
    }

    /**
     * @param $pro_id
     * @return mixed
     */
    public function getTagListByProId($pro_id)
    {
        return $this->tagDao->getAutoOfferProTag($pro_id);
    }

    /**
     * 获取所有标签名
     * @return array
     */
    public function getTagNames()
    {
        $tag_names = [];
        $result = $this->tagDao->getTagListAll();
        if (!empty($result)){
            foreach ($result as $item) {
                $tag_names[] = $item['name'];
            }
        }
        return $tag_names;
    }

    /**
     * 指定类标签
     * @param $cate_ids
     * @return mixed
     */
    public function getTagByCateList($cate_ids)
    {
        return $this->tagDao->getTagByCateList($cate_ids);
    }

    /**
     * 保存热搜
     * @param $hot_list
     * @return mixed
     * @throws MysqlException
     */
    public function hotSearchInsert($hot_list)
    {
        return $this->tagDao->saveHotSearch($hot_list);
    }
}