<?php
namespace App\Models\Data;

use App\Models\Dao\UserSubscriptionTagDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 * 供应商主营产品标签数据类
 * @Bean()
 * @uses      UserSubscriptionTagData
 * @author    yang
 */
class UserSubscriptionTagData
{

    /**
     * 主营产品标签数据对象
     * @Inject()
     * @var UserSubscriptionTagDao
     */
    private $userSubscriptionTagDao;

    /**
     * 根据标签id获取符合条件的供应商
     * @author yang
     * @param $tag_ids
     * @param $fields
     * @return array
     */
    public function getUserIds($tag_ids)
    {
        $fields = ['user_id'];
        $result_ids = [];
        $result = $this->userSubscriptionTagDao->getSubscriptionTagList($tag_ids, $fields);
        if (!empty($result)){
            $user_ids = [];
            foreach ($result as $value) {
                $user_ids[] = $value['userId'];
            }
            $statistics = array_count_values($user_ids);
            $connt = count($tag_ids);
            foreach ($statistics as $key => $value) {
                if ($value==$connt) $result_ids[] = $key;//过滤供应商
            }
        }
        return $result_ids;
    }
}