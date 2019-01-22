<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 * @DESC: 供应商数据报表生成
 */

namespace App\Tasks;

use App\Models\Data\UserData;
use App\Models\Logic\UserLogic;
use Swoft\App;
use Swoft\Bean\Annotation\Inject;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class SupplierDataTask - define some tasks
 *
 * @Task("SupplierData")
 * @package App\Tasks
 */
class SupplierDataTask{

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * 报表数据统计 task
     *  3 o'clock every day
     *
     * @Scheduled(cron="0 34 14 * * *")
     * @throws \Swoft\Db\Exception\DbException
     */
    public function cronTask()
    {
        $date_type = $this->userData->getSetting('supplier_data_cycle');
        $last_days = date('Y-m-d',strtotime("-{$date_type} day"));
        $last_day_time = strtotime($last_days);
        if($last_days > 0){
            $params = [
                ['last_time','>=', $last_day_time],
                ['role','IN',[2,3,4]]
            ];
            /* @var UserLogic $user_logic */
            $user_logic = App::getBean(UserLogic::class);
            $user_logic->supplierDataList($params, $last_day_time, $date_type);
        }
        return ['供应商数据统计'];
    }
}
