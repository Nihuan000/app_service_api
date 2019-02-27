<?php
namespace App\Models\Dao;

use App\Models\Entity\UserSubscriptionTag;
use Swoft\Bean\Annotation\Bean;

/**
 * 供应商主营产品标签
 * @Bean()
 * @uses UserSubscriptionTagDao
 * @author Nihuan
 */
class UserSubscriptionTagDao
{

    /**
     * 获取主营标签
     * @author yang
     * @param array $tag_ids
     * @param array $fields
     * @return mixed
     */
    public function getSubscriptionTagList(array $tag_ids, array $fields)
    {
        return UserSubscriptionTag::findAll(
            [
                'tag_id' => $tag_ids
            ],
            ['fields' => $fields])->getResult();
    }
}