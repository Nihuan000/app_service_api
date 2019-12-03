<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Tasks;

use App\Models\Data\BuyData;
use App\Models\Data\BuyRelationTagData;
use App\Models\Data\ProductSearchRecordData;
use App\Models\Data\TagData;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\MysqlException;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * 热搜关键词统计
 *
 * @Task("HotSearch")
 * @package App\Tasks
 */
class HotSearchTask{

    /**
     * @Inject()
     * @var BuyData
     */
    private $buyData;

    /**
     * @Inject()
     * @var BuyRelationTagData
     */
    private $buyRelationData;

    /**
     * @Inject()
     * @var TagData
     */
    private $tagData;

    /**
     * @Inject()
     * @var ProductSearchRecordData
     */
    private $proSearchData;

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    /**
     * 缓存标签列表
     * @var array
     */
    private $cache_tag_list;

    /**
     * 采购关联标签列表
     * @var array
     */
    private $tag_list_group;

    /**
     * 热度权重值
     * @var array
     */
    private $weight = [
        'relation' => 15,
        'search' => 0.9
    ];

    /**
     * 热度报告生成
     * 每周二0点生成
     *
     * @Scheduled(cron="0 1 3 * * 3")
     */
    public function purchaseReportTask()
    {
        $week = date('W');
        $hot_search_key = 'hot_search_' . $week;
        Log::info('第' . $week . '周热度排行榜任务开始');
        //上周发布采购
        $start_time = strtotime(date('Y-m-d',strtotime('-7 day')));
        $end_time = strtotime(date('Y-m-d')) - 1;
        $params = [
            ['add_time', 'between', $start_time, $end_time],
            'is_audit' => 0
        ];
        $fields = ['buy_id'];
        $buy_list = $this->buyData->getBuyList($params,$fields);
        $buy_ids = [];
        if(!empty($buy_list)){
            foreach ($buy_list as $item) {
                $buy_ids[] = $item['buyId'];
            }

            if(!empty($buy_ids)){
                $tag_params = [
                    'buy_id' => $buy_ids,
                    'cate_id' => [1,4]
                ];
                $buy_tag_list = $this->buyRelationData->getBuyTagByParams($tag_params,['tag_name']);
            }
        }

        //上周搜索标签获取
        $tag_cache = [];
        $tag_list = $this->tagData->getTagByCateList([1,4]);
        if(!empty($tag_list)){
            foreach ($tag_list as $tag) {
                $tag_cache[$tag['tagId']] = $tag['name'];
            }

            if(!empty($tag_cache)){
                $keywords = array_values($tag_cache);
                $search_params = [
                    ['keyword', 'in', $keywords],
                    ['search_time', 'between', $start_time, $end_time],
                ];
                $search_list = $this->proSearchData->getRecordList($search_params,['log_id','keyword']);
            }
        }

        //标签分数计算
        if(!empty($buy_tag_list)){
            foreach ($buy_tag_list as $item) {
                $this->tag_weight($item['tagName'],'relation');
            }
        }

        if(!empty($search_list)){
            foreach ($search_list as $search) {
                $this->tag_weight($search['keyword'],'search');
            }
        }

        //报告缓存
        $data_list = [];
        if(!empty($this->cache_tag_list)){
            foreach ($this->cache_tag_list as $tag => $score) {
                $hot_score = sprintf('%.1f',$score/2);
                $this->redis->zAdd($hot_search_key,$hot_score, $tag);
                $data['tag_name'] = $tag;
                $data['report_date'] = date('Y-m-d');
                $data['procurement'] = isset($this->tag_list_group[$tag]['relation']) ? sprintf('%.1f',$this->tag_list_group[$tag]['relation']) : 0;
                $data['search_heat'] = isset($this->tag_list_group[$tag]['search']) ? sprintf('%.1f',$this->tag_list_group[$tag]['search']) : 0;
                $data['hot_search'] = $hot_score;
                $data['add_time'] = time();
                $data_list[] = $data;
            }
        }

        if(!empty($data_list)){
            try {
                $this->tagData->hotSearchInsert($data_list);
                $this->redis->expire($hot_search_key,7*24*3600);
            } catch (MysqlException $e) {
                Log::info('hot search err:' . $e->getMessage());
            }
        }
        Log::info('第' . $week . '周热度排行榜任务结束');
        return '热度排行榜';
    }

    /**
     * 标签权重计算
     * @param $tag
     * @param $weight_type
     */
    private function tag_weight($tag, $weight_type)
    {
        if(isset($this->cache_tag_list[$tag])){
            $this->cache_tag_list[$tag] += $this->weight[$weight_type];
        }else{
            $this->cache_tag_list[$tag] = $this->weight[$weight_type];
        }

        if(isset($this->tag_list_group[$tag][$weight_type])){
            $this->tag_list_group[$tag][$weight_type] += 1;
        }else{
            $this->tag_list_group[$tag][$weight_type] = 1;
        }
    }
}
