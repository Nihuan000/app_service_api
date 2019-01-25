<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 下午2:35
 */

namespace App\Models\Dao;

use App\Models\Data\BuyData;
use App\Models\Entity\BuyRelationTag;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Bean;
use Swoft\Db\Db;
use Swoft\Db\Query;

/**
 * 采购标签数据对象
 * @Bean()
 * @uses OrderDao
 * @author Nihuan
 */
class BuyRelationTagDao
{

    /**
     * @Inject()
     * @var BuyDao
     */
    private $buyDao;

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

    /**
     * 用户订阅采购数
     * @param int $user_id
     * @param array $buy_days
     * @return int|\Swoft\Core\ResultInterface
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserSubscriptBuy(int $user_id, array $buy_days)
    {
        $buy_count = 0;
        $user_tags = [];
        $subscript_tag_ids = Query::table('sb_user_subscription_tag')->where('user_id',$user_id)->get(['tag_id'])->getResult();
        if(!empty($subscript_tag_ids)){
            $user_tags = array_column($subscript_tag_ids,'tag_id');
        }

        if(!empty($user_tags)){
            $buy_count_list = $this->buyDao->getBuyListByTagList($user_tags, $buy_days);
            $count_list = [];
            if(!empty($buy_count_list)){
                foreach ($buy_count_list as $item) {
                    $count_list[] = $item['buy_id'];
                }
            }
            $buy_count = count($count_list);
        }
        return $buy_count;
    }
}