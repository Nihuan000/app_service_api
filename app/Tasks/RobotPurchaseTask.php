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
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class RobotPurchaseTask- define some tasks
 *
 * @Task("robotPurchaseTask")
 * @package App\Tasks
 */
class RobotPurchaseTask{

    /**
     * A cronTab task
     * 3-5 seconds per minute 每分钟第3-5秒执行
     *
     * @Scheduled(cron="3-5 * * * * *")
     */
    public function cronTask()
    {
        echo time() . "第3-5秒执行\n";

        return 'cron';
    }
}
