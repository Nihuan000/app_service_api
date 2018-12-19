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

use Swoft\App;
// use Swoft\Bean\Annotation\Inject;
// use Swoft\HttpClient\Client;
// use Swoft\Rpc\Client\Bean\Annotation\Reference;
use Swoft\Db\Db;
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

//    /**
//     * @Inject("demoRedis")
//     * @var Redis
//     */
//    private $redis;
//
//    /**
//     * A cronTab task
//     * 每天5点清除7天以前的数据
//     * @Scheduled(cron="0 0 5 * * *")
//     */
//    public function waterFollsExpireTask()
//    {
//        $last_time = strtotime('-7 day');
//        $setting = Db::query("SELECT &")->getResult();
//        $waterfall_index = 'index_water_falls_list_' . $params['cycle'] . '_' . $params['display_count'];
//        $len = $this->searchRedis->lLen($index . $date);
//        $current_list = $this->redis->zRangeByScore($waterfall_index,$current_user_start_time,$current_user_end_time);
//        return ['过期瀑布流数据清除'];
//    }
}
