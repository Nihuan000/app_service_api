<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 下午2:34
 */

namespace App\Models\Data;

use App\Models\Dao\BuyRelationTagDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 * 采购标签数据类
 * @Bean()
 * @uses      BuyRelationTagData
 * @author    Nihuan
 */
class BuyRelationTagData
{
    /**
     * 采购标签数据对象
     * @Inject()
     * @var BuyRelationTagDao
     */
    private $buyRelationTagDao;


    /**
     * 根据采购id获取标签列表
     * @author Nihuan
     * @param $buy_ids
     * @param $fields
     * @return mixed
     */
    public function getRealtionTagByIds($buy_ids, $fields)
    {
        return $this->buyRelationTagDao->getRelationTagList($buy_ids, $fields);
    }


}