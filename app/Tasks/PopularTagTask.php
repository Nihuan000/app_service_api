<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-18
 * Time: 下午4:21
 */

namespace App\Tasks;

use App\Models\Data\TagData;
use App\Models\Entity\Buy;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;
use App\Models\Data\BuyRelationTagData;

/**
 * PopularTag task
 *
 * @Task("PopularTag")
 */
class PopularTagTask
{

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;
    private $limit = 1000;

    /**
     * @Inject()
     * @var BuyRelationTagData
     */
    private $relationTag;

    /**
     * @Inject()
     * @var TagData
     */
    private $tagData;

    protected $tag_stastic_list;

    /**
     * 热门推送标签统计
     * @author Nihuan
     * @Scheduled(cron="0 0 5 1 * *")
     */
    public function statisticTagTask()
    {
        $tag_index = '@RECOMMEND_HOT_TAG_';
        $last_time = strtotime("-60 day");
        $last_id = 0;
        $buyCount = Buy::count('buy_id',['audit_time' => ['>=',$last_time], 'is_audit' => 0, 'push_status' => 3])->getResult();
        $pages = ceil($buyCount/$this->limit);
        if($pages > 0){
            for ($i = 0; $i < $pages; $i++){
                $buy_ids = [];
                $buyResult = Buy::findAll(
                    [['audit_time','>=', $last_time], 'is_audit' => 0, 'push_status' => 3, ['buy_id','>',$last_id]],
                    ['limit' => $this->limit, 'orderBy' => ['buy_id' => 'ASC'], 'fields' => ['buy_id']]
                )->getResult();
                if(!empty($buyResult)){
                    foreach ($buyResult as $item) {
                        $buy_ids[] = $item['buyId'];
                    }
                    $last_id = end($buy_ids);
                    $tags = $this->relationTag->getRealtionTagByIds($buy_ids,['parent_id']);
                    $this->tag_list($tags);
                }
            }
        }
        if(!empty($this->tag_stastic_list)){
            $ranking_list = [];
            arsort($this->tag_stastic_list);
            $tag_list = array_slice($this->tag_stastic_list,0,30);
            $rank_tag = $this->tagData->getRankByParentIds($tag_list,['top_id','name']);
            foreach ($rank_tag as $rank) {
                $ranking_list[$rank['topId']][] = $rank['name'];
            }

            foreach ($ranking_list as $key => $top_rank) {
                $this->redis->set($tag_index . $key, json_encode($top_rank));
            }
        }
    }


    /**
     * 标签列表统计
     * @author Nihuan
     * @param $tag_object
     * @return array
     */
    protected function tag_list($tag_object)
    {
        if(!empty($tag_object)){
            foreach ($tag_object as $tag) {
                if(isset($this->tag_stastic_list[$tag['parentId']])){
                    $this->tag_stastic_list[$tag['parentId']] += 1;
                }else{
                    $this->tag_stastic_list[$tag['parentId']] = 1;
                }
            }
        }
        return $this->tag_stastic_list;
    }
}