<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-19
 * Time: 上午10:59
 */

namespace App\Models\Dao;

use App\Models\Entity\Buy;
use App\Models\Entity\BuyRecords;
use App\Models\Entity\BuyRelationTag;
use Elasticsearch\Endpoints\DeleteByQuery;
use Swoft\Bean\Annotation\Bean;
use Swoft\Db\Db;
use Swoft\Db\Query;

/**
 * 采购数据对象
 * @Bean()
 * @uses BuyDao
 * @author Nihuan
 */
class BuyDao
{
    /**
     * 主键查询一条数据
     * @author Nihuan
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return Buy::findById($id)->getResult();
    }

    /**
     * @param array $condition
     * @param array $data
     * @return \Swoft\Core\ResultInterface
     */
    public function updateById(array $condition, array $data){
        return Buy::updateOne($data,$condition)->getResult();
    }

    /**
     * @author Nihuan
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getNoQuoteBuyDao()
    {
        $day_time = date('Y-m-d',strtotime('-1 day'));
        return Db::query("SELECT b.buy_id AS buyId, b.remark, b.pic, b.amount, b.unit FROM sb_buy b LEFT JOIN sb_buy_attribute AS a ON b.buy_id = a.buy_id WHERE b.status = 0 AND  b.del_status = 1 AND  b.is_audit = 0 AND  FROM_UNIXTIME(b.audit_time,'%Y-%m-%d') = '{$day_time}' AND  b.is_find = 0 AND  a.offer_count = 0")->getResult();
    }

    /**
     * 获取浏览过的采购列表
     * @param $params
     * @return \Swoft\Core\ResultInterface
     */
    public function getVisitBuyRecord($params)
    {
        return BuyRecords::findAll( $params, ['fields' => ['buy_id']])->getResult();
    }

    /**
     * 获取用户采购id列表
     * @param $params
     * @return \Swoft\Core\ResultInterface
     */
    public function getUserBuyIds($params)
    {
        return Buy::findAll( [['add_time','>=',$params['last_time']], 'is_audit' => 0, 'del_status' => 1 ,'user_id' => $params['user_id']],
            ['fields' => ['buy_id','add_time']])->getResult();
    }

    /**
     * 用户搜索产品关键词记录
     * @param $user_id
     * @param $last_time
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getUserSearchLog($user_id, $last_time)
    {
        return Db::query("select search_time,keyword from sb_product_search_log where user_id = {$user_id} and page_num = 1 and keyword <>'' and search_time >= {$last_time}")->getResult();
    }

    /**
     * 根据标签获取采购信息
     * @param $tag_id
     * @param array $current_ids
     * @return mixed
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getBuyInfoByTagId($tag_id,array $current_ids)
    {
        if(!empty($current_ids)){
            $ids = implode(',',$current_ids);
        }else{
            $ids = 0;
        }
        $last_time = strtotime('-3 day');
        return Db::query("SELECT b.* FROM sb_buy b LEFT JOIN sb_buy_relation_tag AS a ON b.buy_id = a.buy_id WHERE b.status = 0 AND b.del_status = 1 AND b.is_audit = 0 AND b.amount >= 100 AND a.tag_id = {$tag_id} AND refresh_time >= {$last_time} AND b.buy_id NOT IN ({$ids}) ORDER BY b.refresh_time DESC LIMIT 1")->getResult();
    }

    /**
     * 订阅采购数获取
     * @param $tag_ids
     * @param $day_list
     * @return \Swoft\Core\ResultInterface
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getBuyListByTagList($tag_ids, $day_list)
    {
        $buy_count = Query::table('sb_buy')->leftJoin('sb_buy_relation_tag',"sb_buy.buy_id = rt.buy_id",'rt')->whereIn('rt.tag_id',$tag_ids)->whereIn("from_unixtime(sb_buy.add_time,'%Y-%m-%d')",$day_list)->groupBy('sb_buy.buy_id')->get(['sb_buy.buy_id'])->getResult();
        return $buy_count;
    }

    /**
     * 发布成功采购数
     * @param $user_id
     * @return \Swoft\Core\ResultInterface
     */
    public function getBuyCount($user_id)
    {

        return Buy::count('buy_id', ['user_id' => $user_id, 'is_audit'=>0, 'del_status'=>1])->getResult();
    }

    /**
     * 采纳报价数
     * @param $user_id
     * @return \Swoft\Core\ResultInterface
     * @throws \Swoft\Db\Exception\DbException
     */
    public function getOfferCount($user_id)
    {
        $result = Query::table('sb_buy')->innerJoin('sb_offer','sb_buy.buy_id = sb_offer.buy_id')
            ->where('sb_buy.user_id',$user_id)
            ->where('sb_buy.is_audit',0)
            ->where('sb_buy.del_status',1)
            ->where('sb_offer.is_audit',1)
            ->where('sb_offer.is_matching',1)
            ->count('sb_offer.buy_id')->getResult();
        return $result;
    }
}