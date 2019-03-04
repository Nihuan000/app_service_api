<?php
namespace App\Models\Dao;

use App\Models\Entity\Tag;
use App\Models\Entity\UserStrength;
use Swoft\Bean\Annotation\Bean;

/**
 * 采购数据对象
 * @Bean()
 * @uses UserStrengthDao
 * @author yang
 */
class UserStrengthDao
{

    /**
     * @author yang
     * @param array $parent_ids
     * @param array $fields
     * @return mixed
     */
    public function getStrengInfo(array $user_id, array $fields)
    {
        return UserStrength::findAll(
            [
                'tag_id' => $user_id
            ],
            ['fields' => $fields])->getResult();
    }
}