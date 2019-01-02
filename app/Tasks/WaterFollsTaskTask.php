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

use App\Models\Data\UserData;
use Swoft\Bean\Annotation\Inject;
use Swoft\Log\Log;
use Swoft\Redis\Redis;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class WaterFollsTaskTask - define some tasks
 *
 * @Task("WaterFolls")
 * @package App\Tasks
 */
class WaterFollsTaskTask{

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * 每天5点清除一个月以前的数据
     * @Scheduled(cron="0 0 5 * * *")
     */
    public function waterFollsExpireTask()
    {
        $last_time = strtotime('-1 month');
        $display_filter_num = $this->userData->getSetting('pro_display_fliter_number');
        $product_count = $this->userData->getSetting('pro_display_number');
        $waterfall_index = 'index_water_falls_list_' . $display_filter_num . '_' . $product_count;
        $remRes = $this->redis->zRemRangeByScore($waterfall_index,0,$last_time);
        Log::info('瀑布流过期数据清除数:' . (int)$remRes);
        return ['过期瀑布流数据清除'];
    }
}
