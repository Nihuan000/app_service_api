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

    private $limit = 500;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * 报表数据统计 task
     *  1 o'clock every day
     *
     * @Scheduled(cron="0 01 00 * * 01")
     * @return array
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

    public function sendTask()
    {
        $send_switch = $this->userData->getSetting('supplier_data_send');
        if($send_switch == 1){
            $send_cover = $this->userData->getSetting('supplier_data_cover');
            $last_time = strtotime(date('Y-m-d'));
            $condition = [
                ['record_time','>=',$last_time],
                'send_status' => 0,
                'send_time' => 0
            ];
            $count = $this->userData->getSupplierCount($condition);
            if($count > 0){
                $pages = ceil($count/$this->limit);
                if($pages > 0){
                    $last_id = 0;
                    for ($i = 0; $i <= $pages; $i++){
                        $params = [
                            ['sds_id','>',$last_id]
                        ];
                        $condition[] = $params;
                        $list = $this->userData->getSupplierData($condition,$this->limit);
                        if(!empty($list)){
                            foreach ($list as $item) {

                                $last_id = $item['sdsId'];
                            }
                        }
                    }
                }
            }
        }
    }
}
