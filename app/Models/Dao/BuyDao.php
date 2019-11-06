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
use Swoft\Core\ResultInterface;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
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
     * 获取信息
     * @param array $params
     * @param array $fields
     * @return array
     * @author yang
     */
    public function getBuyList(array $params,array $fields)
    {
        return Buy::findAll($params,['fields' => $fields])->getResult();
    }

    /**
     * @param array $condition
     * @param array $data
     * @return ResultInterface
     */
    public function updateById(array $condition, array $data){
        return Buy::updateOne($data,$condition)->getResult();
    }

    /**
     * @author Nihuan
     * @return mixed
     * @throws DbException
     */
    public function getNoQuoteBuyDao()
    {
        $day_time = date('Y-m-d',strtotime('-1 day'));
        return Db::query("SELECT b.buy_id AS buyId, b.remark, b.pic, b.amount, b.unit FROM sb_buy b LEFT JOIN sb_buy_attribute AS a ON b.buy_id = a.buy_id WHERE b.status = 0 AND  b.del_status = 1 AND  b.is_audit = 0 AND  FROM_UNIXTIME(b.audit_time,'%Y-%m-%d') = '{$day_time}' AND  b.is_find = 0 AND  a.offer_count = 0")->getResult();
    }

    /**
     * 获取浏览过的采购列表
     * @param $params
     * @return ResultInterface
     */
    public function getVisitBuyRecord($params)
    {
        return BuyRecords::findAll( $params, ['fields' => ['buy_id']])->getResult();
    }

    /**
     * 获取用户采购id列表
     * @param $params
     * @return ResultInterface
     */
    public function getUserBuyIds($params)
    {
        return Buy::findAll( [['add_time','>=',$params['last_time']], 'is_audit' => 0, 'del_status' => 1 ,'user_id' => $params['user_id']], ['fields' => ['buy_id','add_time']])->getResult();
    }

    /**
     * 用户搜索产品关键词记录
     * @param $user_id
     * @param $last_time
     * @return mixed
     * @throws DbException
     */
    public function getUserSearchLog($user_id, $last_time)
    {
        return Db::query("select search_time,keyword from sb_product_search_log where user_id = {$user_id} and page_num = 1 and keyword <>'' AND is_new != 4 and search_time >= {$last_time}")->getResult();
    }

    /**
     * 根据标签获取采购信息
     * @param $tag_id
     * @param array $current_ids
     * @return mixed
     * @throws DbException
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
     * @return ResultInterface
     * @throws DbException
     */
    public function getBuyListByTagList($tag_ids, $day_list)
    {
        $buy_count = Query::table('sb_buy')->leftJoin('sb_buy_relation_tag',"sb_buy.buy_id = rt.buy_id",'rt')->whereIn('rt.tag_id',$tag_ids)->whereIn("from_unixtime(sb_buy.add_time,'%Y-%m-%d')",$day_list)->groupBy('sb_buy.buy_id')->get(['sb_buy.buy_id'])->getResult();
        return $buy_count;
    }

    /**
     * 发布成功采购数
     * @param $user_id
     * @return ResultInterface
     */
    public function getBuyCount($user_id)
    {

        return Buy::count('buy_id', ['user_id' => $user_id, 'is_audit'=>0, 'del_status'=>1])->getResult();
    }

    /**
     * 采纳报价数
     * @param $user_id
     * @return ResultInterface
     * @throws DbException
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

    /**
     * 获取用户最新发布的采购信息，group +max 貌似效果不理想，先多取，数组过滤
     * @param array $params
     * @return mixed
     */
    public function getLastBuyIds(array $params)
    {
        return Buy::findAll($params,['fields' => ['user_id','buy_id','add_time'], 'orderby' => ['add_time' => 'desc']])->getResult();
    }

    /**
     * 采购访问记录
     * @param array $data
     * @return mixed
     */
    public function setBuyVisitLog(array $data)
    {
        $record = new BuyRecords();
        $exists = $record::findOne(['user_id' => $data['user_id'], 'r_time' => $data['r_time'],'buy_id' => $data['buy_id']])->getResult();
        if($exists){
            return true;
        }
        $record->setUserId($data['user_id']);
        $record->setBuyId($data['buy_id']);
        $record->setRTime($data['r_time']);
        $record->setScene($data['scene']);
        $record->setIsFilter($data['is_filter']);
        $record->setFromType($data['from_type']);

        return $record->save()->getResult();
    }

    /**
     * 修改产品信息
     * @param int $id
     * @return mixed
     */
    public function updateBuyClickById(int $id)
    {
        $proInfo = Buy::findById($id)->getResult();
        if(!empty($proInfo)){
            $data = [
                'clicks' => $proInfo['clicks'] + 1,
                'alter_time' => time()
            ];
            return Buy::updateOne($data,['pro_id' => $id])->getResult();
        }
        return false;
    }
}
