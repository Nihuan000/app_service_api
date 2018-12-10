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
     * @Inject()
     * @var TagData
     */
    private $tagData;

    /**
     * 二级品类列表
     * @var array
     */
    protected $pro_cate = [ '绒布', '梭织提花', '灯芯绒', '麻棉弹力', '羽绒服','棉服','羽绒服、棉服'];

    /**
     * 搜索指定列表
     * @var array
     */
    protected $search_key = ['印花','时装','格纹','牛仔','粗纺','网布','衬衫','雪纺','风衣裤'];

    /**
     * 顶级类列表
     * @var array
     */
    protected $top_tag = ['呢料毛纺','蕾丝','针织','蕾丝绣品'];

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
     * @param $black_ids
     * @return array
     */
    public function getUserVisitBuyTag($uid,$last_time,$black_ids)
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
            $tags = $this->buyRelationTagDao->getRelationTagList($buy_ids,['tag_name'],$black_ids);
            if(!empty($tags)){
                $tag_list = [];
                foreach ($tags as $tag) {
                    if(isset($tag_list[$tag['tagName']])){
                        $tag_list[$tag['tagName']] += 1;
                    }else{
                        $tag_list[$tag['tagName']] = 1;
                    }
                }
                $tag_ids = array_keys($tag_list);
                return array_slice($tag_ids,0,10);
            }
        }
        return [];
    }

    /**
     * 报价的采购标签
     * @param $uid
     * @param $last_time
     * @param $black_ids
     * @return array
     */
    public function getUserOfferBid($uid,$last_time,$black_ids)
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
            $tags = $this->buyRelationTagDao->getRelationTagList($buy_ids,['tag_name'],$black_ids);
            if(!empty($tags)){
                $tag_list = [];
                foreach ($tags as $tag) {
                    if(isset($tag_list[$tag['tagName']])){
                        $tag_list[$tag['tagName']] += 1;
                    }else{
                        $tag_list[$tag['tagName']] = 1;
                    }
                }
                $tag_ids = array_keys($tag_list);
                return array_slice($tag_ids,0,10);
            }
        }
        return [];
    }

    /**
     * 发布采购标签获取
     * @param $user_id
     * @return array
     */
    public function getUserBuyIdsHalfYear($user_id)
    {
        $relation_tags = [];
        $last_time = strtotime('-3 month');
        $params = [
            'user_id' => $user_id,
            'last_time' => $last_time
        ];
        $buy_ids = $this->buyDao->getUserBuyIds($params);
        if(!empty($buy_ids)){
            $id_list = [];
            foreach ($buy_ids as $buy_id) {
                $id_list[] = $buy_id['buyId'];
            }
            $buy_tags = $this->buyRelationTagDao->getRelationTagList($id_list,['top_name','parent_name','buy_id']);
            if(!empty($buy_tags)){
                $cache_buy_tag = [];
                foreach ($buy_tags as $tag) {
                    $tag_name = [];
                    $top_keyword = str_replace('面料','',$tag['topName']);
                    $parent_keyword = str_replace('面料','',$tag['parentName']);
                    if(in_array($top_keyword,$this->top_tag)){
                        $tag_name = $top_keyword;
                    }
                    if(in_array($parent_keyword,$this->pro_cate)){
                        $tag_name = $parent_keyword;
                    }
                    if(!empty($tag_name) && !isset($cache_buy_tag[$tag['buyId']])){
                        $cache_buy_tag[$tag['buyId']] = 1;
                        $relation_tags[$tag_name][] = 100;
                    }
                }
            }
        }
        return $relation_tags;
    }

    /**
     * @param $user_id
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserSearchKeyword($user_id)
    {
        $search_tag = [];
        $last_time = strtotime('-1 month');
        $keyword_list = $this->buyDao->getUserSearchLog($user_id,$last_time);
        if(!empty($keyword_list)){
            foreach ($keyword_list as $item) {
                if(!empty($item['keyword'])){
                    $keyword = str_replace('面料','',$item['keyword']);
                    if(in_array($keyword,$this->search_key) || in_array($keyword,$this->pro_cate) || in_array($keyword,$this->top_tag)){
                        $search_tag[$keyword][] = 30;
                    }
                }
            }
        }
        return $search_tag;
    }

}