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
use Swoft\Core\ResultInterface;
use Swoft\Db\Exception\DbException;

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
     * 时间维度
     * @var array
     */
    protected $timeRank = [86400 => 10000,604800 => 100];

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
     * 获取采购信息列表
     * @author yang
     * @param $params
     * @param $fields
     * @return array
     */
    public function getBuyList(array $params,array $fields)
    {
        return $this->buyDao->getBuyList($params, $fields);
    }

    /**
     * 0报价采购信息获取
     * @author Nihuan
     * @return mixed
     * @throws DbException
     */
    public function getNoQuoteBuy()
    {
        return $this->buyDao->getNoQuoteBuyDao();
    }

    /**
     * 根据采购id修改信息
     * @param int $bid
     * @param array $params
     * @return ResultInterface
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
        $last_time = strtotime('-2 month');
        $params = [
            'user_id' => $user_id,
            'last_time' => $last_time
        ];
        $buy_ids = $this->buyDao->getUserBuyIds($params);
        if(!empty($buy_ids)){
            $id_list = [];
            $buy_score_list = [];
            foreach ($buy_ids as $buy_id) {
                $id_list[] = $buy_id['buyId'];
                $buy_score_list[$buy_id['buyId']] = $buy_id['addTime'];
            }
            $buy_tags = $this->buyRelationTagDao->getRelationTagList($id_list,['top_name','parent_name','buy_id']);
            if(!empty($buy_tags)){
                $cache_buy_tag = [];
                $now_time = time();
                foreach ($buy_tags as $tag) {
                    $tag_name = '';
                    $parent_keyword = str_replace('面料','',$tag['parentName']);
                    $tag_name = $parent_keyword;
                    if(!empty($tag_name) && !isset($cache_buy_tag[$tag['buyId']])){
                        $tag_score = 100;
                        $tag_mictime = $now_time - $buy_score_list[$tag['buyId']];
                        $value_add = similar_acquisition($tag_mictime,$this->timeRank);
                        if(!empty($value_add)){
                            $tag_score *= $value_add;
                        }
                        $cache_buy_tag[$tag['buyId']] = 1;
                        $relation_tags[$tag_name][] = $tag_score;
                    }
                }
            }
        }
        return $relation_tags;
    }

    /**
     * @param $user_id
     * @return array
     * @throws DbException
     */
    public function getUserSearchKeyword($user_id)
    {
        $search_tag = [];
        $last_time = strtotime('-1 month');
        $keyword_list = $this->buyDao->getUserSearchLog($user_id,$last_time);
        if(!empty($keyword_list)){
            $now_time = time();
            foreach ($keyword_list as $item) {
                if(!empty($item['keyword'])){
                    $keyword = str_replace('面料','',$item['keyword']);
                    $tag_score = 30;
                    $tag_mictime = $now_time - $item['search_time'];
                    $value_add = similar_acquisition($tag_mictime,$this->timeRank);
                    if(!empty($value_add)){
                        $tag_score *= $value_add;
                    }
                    $search_tag[$keyword][] = $tag_score;
                }
            }
        }
        return $search_tag;
    }

    /**
     * 获取发布采购成功数
     * @param $user_id
     * @return int|ResultInterface
     * @author yang
     */
    public function getBuyCount($user_id)
    {
        $count = $this->buyDao->getBuyCount($user_id);
        if (isset($count)){
            return $count;
        }else{
            return 0;
        }

    }

    /**
     * 获取发布采购成功数
     * @param $user_id
     * @return int|ResultInterface
     *@throws DbException
     * @author yang
     */
    public function getOfferCount($user_id)
    {
        $count = $this->buyDao->getOfferCount($user_id);
        if (isset($count)){
            return $count;
        }else{
            return 0;
        }

    }

    /**
     * 符合条件的最大采购id组
     * @param $params
     * @return mixed
     */
    public function getLastBuyIds($params)
    {
        $buy_ids = [];
        $user_list = [];
        $buy_ids_result = $this->buyDao->getLastBuyIds($params);
        if(!empty($buy_ids_result)){
            foreach ($buy_ids_result as $buy) {
                if(in_array($buy['userId'],$user_list)){
                    continue;
                }
                $buy_ids[] = $buy['buyId'];
                $user_list[] = $buy['userId'];
            }
        }
        return $buy_ids;
    }

    /**
     * 用户某个时间点之后发布的采购
     * @param $user_id
     * @param $last_time
     * @return ResultInterface
     */
    public function getUserByIds($user_id,$last_time)
    {
        $params = [
            'user_id' => $user_id,
            'last_time' => $last_time
        ];
        return $this->buyDao->getUserBuyIds($params);
    }


    /**
     * 采购访问记录添加
     * @param array $data
     * @return bool
     */
    public function setBuyRecordLog(array $data)
    {
        //记录
        $record = $this->buyDao->setBuyVisitLog($data);
        if($record == 0){
            return true;
        }
        //点击量更新
        $clicks = $this->buyDao->updateBuyClickById($data['buy_id']);
        if($record && $clicks){
            return true;
        }
        return false;
    }

}
