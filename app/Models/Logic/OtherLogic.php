<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Logic;

use App\Models\Data\OrderData;
use App\Models\Data\OtherData;
use App\Models\Data\UserData;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Redis\Redis;

/**
 *
 * @Bean()
 * @uses      OtherLogic
 */
class OtherLogic
{
    /**
     * @Inject()
     * @var OtherData
     */
    private $otherData;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;


    /**
     * 记录写入
     * @param array $records
     * @return mixed
     */
    public function activate_sms_records($records = [])
    {
        return $this->otherData->saveRecords($records);
    }

    public function inactive_user_list($limit = 20, $days = 7)
    {
        $user_list = [];
        $last_id = 0;
        $last_time = strtotime(date('Y-m-d',strtotime("-{$days} day")));
        $prev_time = strtotime(date('Y-m-d 23:59:59',strtotime("-{$days} day")));
        $params = [
            ['last_time', '>=',$last_time],
            ['last_time', '<=',$prev_time],
            'role' => [2,3,4],
            'status' => 1
        ];
        //取总数
        $user_count = $this->userData->getUserCountByParams($params);
        $pages = ceil($user_count/$limit);
        if($pages >= 1){
            //253短信最多支持1000个号码，这里提前分页
            for ($i=0; $i<$pages;$i++)
            {
                $params = [
                    ['last_time', '>=',$last_time],
                    ['last_time', '<=',$prev_time],
                    'role' => [2,3,4],
                    'status' => 1,
                    ['user_id','>',$last_id]
                ];
                $tmp_list = $this->userData->getListByParams($params,$limit);
                $user_list[] = $tmp_list;
                $last_info = end($user_list);
                $last_id = $last_info['userId'];
            }
        }
        return $user_list;
    }

    /**
     * 召回用户id记录
     * @return array
     */
    public function activation_user_ids()
    {
        $login_count = 0;
        $last_time = strtotime(date('Y-m-d',strtotime("-1 day")));
        $prev_time = strtotime(date('Y-m-d'));
        $params = [
            ['send_time', '>=',$last_time],
            ['send_time', '<',$prev_time],
            'send_status' => 1,
            'msg_type' => 8,
        ];

        $user_list = $this->otherData->getUserRecords($params,['user_id']);
        $send_total = count($user_list);
        if(!empty($user_list)){
            $userIds = array_column($user_list,'userId');
            $login_user = $this->userData->getUserByUids($userIds,['user_id','last_time']);
            if(!empty($login_user)){
                foreach ($login_user as $item) {
                    if($item['lastTime'] > $last_time){
                        $login_count += 1;
                    }
                }
            }
        }
        return ['send_total' => $send_total, 'login_count' => $login_count];
    }

}
