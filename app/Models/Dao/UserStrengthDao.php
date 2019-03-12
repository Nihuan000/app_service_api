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
     * 批量获取
     * @author yang
     * @param array $parent_ids
     * @param array $fields
     * @return mixed
     */
    public function getStrengInfoAll(array $params, array $fields)
    {
        return UserStrength::findAll($params, ['fields' => $fields])->getResult();
    }

    /**
     * 单条数据
     * @author yang
     * @param array $user_id
     * @param array $fields
     * @return mixed
     */
    public function getStrengInfoOne(array $params, array $fields)
    {
        return UserStrength::findOne($params, ['fields' => $fields])->getResult();
    }
}