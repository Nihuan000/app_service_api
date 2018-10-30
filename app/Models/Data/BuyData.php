<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Data;

use App\Models\Dao\BuyDao;
use App\Models\Dao\BuyRelationTagDao;
use App\Models\Dao\OfferDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;

/**
 *
 * @Bean()
 * @uses      BuyData
 * @author    Nihuan
 */
class BuyData
{

    /**
     * @Inject()
     * @var BuyDao
     */
    protected $buyDao;

    /**
     * 采购标签数据对象
     * @Inject()
     * @var BuyRelationTagDao
     */
    private $buyRelationTagDao;

    /**
     * @Inject()
     * @var OfferDao
     */
    private $offerDao;

    /**
     * 获取采购信息
     * @author Nihuan
     * @param int $bid
     * @return mixed
     */
    public function getBuyInfo(int $bid)
    {
        return $this->buyDao->findById($bid);
    }


    /**
     * 0报价采购信息获取
     * @author Nihuan
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getNoQuoteBuy()
    {
        return $this->buyDao->getNoQuoteBuyDao();
    }

    /**
     * 根据采购id修改信息
     * @param int $bid
     * @param array $params
     * @return \Swoft\Core\ResultInterface
     */
    public function updateBuyInfo(int $bid, array $params)
    {
        $condition['buy_id'] = $bid;
        return $this->buyDao->updateById($condition,$params);
    }

    /**
     * 获取用户浏览过的采购信息
     * @param $uid
     * @param $last_time
     * @return array
     */
    public function getUserVisitBuyTag($uid,$last_time)
    {
        $condition = [
            'user_id' => $uid,
            ['r_time','>=',$last_time]
        ];
        $buy_list = $this->buyDao->getVisitBuyRecord($condition);
        if(!empty($buy_list)){
            $buy_ids = [];
            foreach ($buy_list as $item) {
                $buy_ids[] = $item['buyId'];
            }
            $tags = $this->buyRelationTagDao->getRelationTagList($buy_ids,['tag_id']);
            if(!empty($tags)){
                $tag_list = [];
                foreach ($tags as $tag) {
                    if(isset($this->tag_stastic_list[$tag['parentId']])){
                        $tag_list[$tag['tagId']] += 1;
                    }else{
                        $tag_list[$tag['tagId']] = 1;
                    }
                }
                return array_slice($tag_list,0,10);
            }
        }
        return [];
    }

    /**
     * 报价的采购标签
     * @param $uid
     * @param $last_time
     * @return array
     */
    public function getUserOfferBid($uid,$last_time)
    {
        $condition = [
            'user_id' => $uid,
            ['offer_time','>=',$last_time]
        ];
        $offer_buy_list = $this->offerDao->getUserOfferBid($condition);
        if(!empty($offer_buy_list)){
            $buy_ids = [];
            foreach ($offer_buy_list as $item) {
                $buy_ids[] = $item['buyId'];
            }
            $tags = $this->buyRelationTagDao->getRelationTagList($buy_ids,['tag_id']);
            if(!empty($tags)){
                $tag_list = [];
                foreach ($tags as $tag) {
                    if(isset($this->tag_stastic_list[$tag['parentId']])){
                        $tag_list[$tag['tagId']] += 1;
                    }else{
                        $tag_list[$tag['tagId']] = 1;
                    }
                }
                return array_slice($tag_list,0,10);
            }
        }
        return [];
    }

}