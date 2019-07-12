<?php
namespace App\Models\Dao;

use App\Models\Data\UserData;
use App\Models\Entity\User;
use App\Models\Entity\UserScore;
use App\Models\Entity\UserScoreGetRecord;
use App\Models\Entity\UserScoreLevelRule;
use App\Models\Entity\UserScoreLevelUpdateRecord;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Core\ResultInterface;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Query;
use Swoft\Log\Log;
use Swoft\Redis\Redis;

/**
 * 采购数据对象
 * @Bean()
 * @uses ScoreDao
 * @author Nihuan
 */
class ScoreDao
{
    /**
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * 当前可用积分规则获取
     * @return array
     */
    public function getScoreRuleList()
    {
        $score_rule = [];
        $now_time = time();
        $rule_list = Query::table('sb_user_score_get_rule')
            ->where('end_time',$now_time,'>')
            ->where('start_time',$now_time,'<=')
            ->where('is_enable',1)
            ->where('is_delete',0)
            ->get(['id','rule_key','rule_name','rule_desc','value','month_limit','is_have_ext_score'])
            ->getResult();
        if(!empty($rule_list)){
            foreach ($rule_list as $item) {
                $rule_info = [
                    'id' => $item['id'],
                    'rule_key' => $item['rule_key'],
                    'rule_name' => $item['rule_name'],
                    'rule_desc' => $item['rule_desc'],
                    'value' => $item['value'],
                    'month_limit' => $item['month_limit'],
                    'is_have_ext_score' => $item['is_have_ext_score'],
                ];
                if($item['is_have_ext_score'] == 1){
                    $ext_score = Query::table('sb_user_score_get_rule_ext')->where('rule_id',$item['id'])->one(['every_order_price','ext_value'])->getResult();
                    if(!empty($ext_score)){
                        $rule_info['every_order_price'] = $ext_score['every_order_price'];
                        $rule_info['ext_value'] = $ext_score['ext_value'];
                    }
                }

                $score_rule[$rule_info['rule_key']] = $rule_info;
            }
        }
        return $score_rule;
    }

    /**
     * 产品当月总积分
     * @param int $user_id
     * @param string $month
     * @param int $rule_id
     * @return mixed
     */
    public function getProScoreSum(int $user_id, string $month, int $rule_id)
    {
        $start_time = strtotime($month);
        $end_time = strtotime(date('Y-m',strtotime('+1 month')));
        $score_record = Query::table('sb_user_score_get_record')
            ->where('user_id',$user_id)
            ->where('get_rule_id',$rule_id)
            ->where('add_time',$start_time,'>=')
            ->where('add_time',$end_time,'<')
            ->where('is_valid',1)
            ->sum('score_value','total_value')
            ->getResult();

        return $score_record;
    }

    /**
     * 用户积分总分
     * @param $user_id
     * @return mixed
     */
    public function getUserScore($user_id)
    {
        return UserScore::findOne(['user_id' => $user_id],['fields' => ['user_id','score_value','base_score_value','level_id','level_name']])->getResult();
    }

    /**
     * 获取符合条件的积分记录
     * @param array $params
     * @return mixed
     */
    public function getScoreRecordByParams(array $params)
    {
        return UserScoreGetRecord::findOne($params,['orderby' => ['id' => 'desc']])->getResult();
    }

    /**
     * 积分记录获取
     * @param array $params
     * @return ResultInterface
     */
    public function getScoreCountByParams(array $params)
    {
        return UserScoreGetRecord::count('*',$params)->getResult();
    }

    /**
     * 用户积分更新任务
     * @param array $data
     * @param int $record_id 记录id,目前仅用于判断是否续费实商
     * @param int $is_strength
     * @param int $is_safe_price
     * @return bool
     * @throws DbException
     */
    public function userScoreTask(array $data,int $record_id, int $is_strength,int $is_safe_price)
    {
        $updateScoreRes = true;
        $updateUserRes = true;
        $UserLevelRec = true;
        $now_time = time();
        Db::beginTransaction();
        //写入/更新积分记录
        if($record_id > 0){
            $recordRes = UserScoreGetRecord::updateOne($data,['id' => $record_id])->getResult();
        }else{
            $record = new UserScoreGetRecord();
            $recordRes = $record->fill($data)->save()->getResult();
        }
        //更新用户总积分
        $userScoreRes = true;
        $total_score = 0;
        $old_score = 0;
        $current_score = $this->getUserScore($data['user_id']);
        $level_id = 0;
        if($record_id == 0){
            if(!empty($current_score)){
                if($is_safe_price == 1){
                    $score['base_score_value'] = $data['score_value'];
                    $total_score = $current_score['scoreValue'] + $score['base_score_value'];
                }else{
                    $score['score_value'] = $data['new_score'];
                    $total_score = $data['new_score'] + $current_score['baseScoreValue'];
                }
                $score['update_time'] = $now_time;
                $userScoreRes = UserScore::updateOne($score,['user_id' => $data['user_id']])->getResult();

                $old_score = $current_score['scoreValue'] + $current_score['baseScoreValue'];
            }
            //更新用户等级
            $condition = [
                'user_type' => 1,
                'is_enable' => 1,
                'is_delete' => 0,
                'is_pay' => 0,
                ['min_score','<=',$total_score]
            ];
            $new_level = UserScoreLevelRule::findOne($condition,['orderby' => ['min_score' => 'desc'], 'fields' => ['id','level_name','sort']])->getResult();

            if($current_score['levelId'] != $new_level['sort']){
                $levelUpRes = $this->userLevelUpdateOperate($data,$new_level,$is_strength,$recordRes,$old_score,$current_score,$total_score);
                $updateScoreRes = $levelUpRes['updateScoreRes'];
                $updateUserRes = $levelUpRes['updateUserRes'];
                $UserLevelRec = $levelUpRes['UserLevelRec'];
                $level_id = $levelUpRes['level_id'];
            }
        }

        Log::info('recordRes:' . $recordRes);
        Log::info('userScoreRes:' . $userScoreRes);
        Log::info('updateScoreRes:' . $updateScoreRes);
        Log::info('updateUserRes:' . $updateUserRes);
        Log::info('UserLevelRec:' . $UserLevelRec);
        if($recordRes && $userScoreRes && $updateScoreRes && $updateUserRes && $UserLevelRec){
            Db::commit();
            return $level_id;
        }else{
            Db::rollback();
            return false;
        }
    }

    /**
     * 扣除积分操作
     * @param array $data
     * @param int $is_strength
     * @param int $is_safe_price
     * @param int $is_send_notice
     * @return bool|int|mixed
     * @throws DbException
     */
    public function userScoreDeduction(array $data,int $is_strength,int $is_safe_price,int $is_send_notice = 1)
    {
        $updateScoreRes = true;
        $updateUserRes = true;
        $UserLevelRec = true;
        $now_time = time();
        Db::beginTransaction();
        //写入/更新积分记录
        $record = new UserScoreGetRecord();
        $recordRes = $record->fill($data)->save()->getResult();
        //更新用户总积分
        $userScoreRes = true;
        $total_score = 0;
        $old_score = 0;
        $current_score = $this->getUserScore($data['user_id']);
        $level_id = 0;
        if(!empty($current_score)){
            if($is_safe_price == 1){
                $score['base_score_value'] = $current_score['baseScoreValue'] + $data['score_value'];
                $total_score = $current_score['scoreValue'] + $score['base_score_value'];
            }else{
                $score['score_value'] = $data['new_score'];
                $total_score = $data['new_score'] + $current_score['baseScoreValue'];
            }
            $score['update_time'] = $now_time;
            $userScoreRes = UserScore::updateOne($score,['user_id' => $data['user_id']])->getResult();

            $old_score = $current_score['scoreValue'] + $current_score['baseScoreValue'];
        }
        //更新用户等级
        $condition = [
            'user_type' => 1,
            'is_enable' => 1,
            'is_delete' => 0,
            'is_pay' => 0,
            ['min_score','<=',$total_score]
        ];
        $new_level = UserScoreLevelRule::findOne($condition,['orderby' => ['min_score' => 'desc'], 'fields' => ['id','level_name','sort']])->getResult();

        if($current_score['levelId'] != $new_level['sort']){
            $levelUpRes = $this->userLevelUpdateOperate($data,$new_level,$is_strength,$recordRes,$old_score,$current_score,$total_score,$is_send_notice);
            $updateScoreRes = $levelUpRes['updateScoreRes'];
            $updateUserRes = $levelUpRes['updateUserRes'];
            $UserLevelRec = $levelUpRes['UserLevelRec'];
            $level_id = $levelUpRes['level_id'];
        }

        Log::info('recordRes:' . $recordRes);
        Log::info('userScoreRes:' . $userScoreRes);
        Log::info('updateScoreRes:' . $updateScoreRes);
        Log::info('updateUserRes:' . $updateUserRes);
        Log::info('UserLevelRec:' . $UserLevelRec);
        if($recordRes && $userScoreRes && $updateScoreRes && $updateUserRes && $UserLevelRec){
            Db::commit();
            return $level_id;
        }else{
            Db::rollback();
            return false;
        }
    }

    /**
     * 用户积分等级变更操作
     * @param array $data 要更新数据
     * @param array $new_level 当前积分对应的等级信息
     * @param int $is_strength 是否实商 1:是 0:否
     * @param int $recordRes 积分记录id
     * @param int $old_score 更新前积分
     * @param int $current_score 老的等级积分信息
     * @param int $total_score 总积分 score_value + base_score_value
     * @param int $is_send_notice 是否发送等级变更消息 0:否 1：是
     * @return array
     */
    private function userLevelUpdateOperate($data,$new_level,$is_strength,$recordRes,$old_score,$current_score,$total_score,$is_send_notice = 1)
    {
        $updateScoreRes = true;
        $updateUserRes = true;
        $UserLevelRec = true;
        $level_id = 0;
        $now_time = time();
        $gold_level_sort = 4;//金牌
        $is_agent = $this->userDao->isAgentUser($data['user_id']);
        //内部账号最多只能是金牌 | 实商最低金牌
        if((!empty($is_agent) && $is_agent['uid'] && $new_level['sort'] > $gold_level_sort) || ($is_strength && $new_level['sort'] < $gold_level_sort)){
            $levelData['level_id'] = $gold_level_sort;
            $levelData['level_name'] = '金牌';
            $levelData['update_time'] = $now_time;
        }else{
            //其他情况
            $levelData['level_id'] = $new_level['sort'];
            $levelData['level_name'] = $new_level['levelName'];
            $levelData['update_time'] = $now_time;
        }

        //执行等级变化操作(修改积分等级|用户等级|等级变动记录|发送等级变动通知)
        if(!empty($levelData)){
            //修改积分等级
            $updateScoreRes = UserScore::updateOne($levelData,['user_id' => $data['user_id']])->getResult();

            //用户表等级更新
            $new_user_level = $levelData['level_id'] > 0 ? $levelData['level_id']-1 : 0;
            $userData['level'] = $new_user_level;
            $userData['alter_time'] = $now_time;
            $updateUserRes = User::updateOne($userData,['user_id' => $data['user_id']])->getResult();


            //记录等级变动
            $level_update_record_data = [
                'score_get_record_id' => $recordRes,
                'user_id' => $data['user_id'],
                'old_score' => $old_score,
                'old_level_id'=>(int)$current_score['levelId'],
                'old_level_name'=>(string)$current_score['levelName'],
                'new_score'=> $total_score,
                'new_level_id'=>(int)$levelData['level_id'],
                'new_level_name'=>(string)$levelData['level_name'],
                'opt_user_id'=>$data['opt_user_id'],
                'opt_user_type'=>$data['opt_user_type'],
                'is_auto'=>1,
                'add_time'=>$now_time,
            ];
            $UserLevelModel = new UserScoreLevelUpdateRecord();
            $UserLevelRec = $UserLevelModel->fill($level_update_record_data)->save()->getResult();

            if($is_send_notice == 1){
                ###### 发送等级变动通知开始 ######
                $notice_href_keyword = "查看特权";//通知跳转的关键词
                if($levelData['level_id'] > $current_score['levelId']){
                    $msg_title = "升级通知";
                    $content  = "恭喜您晋升到".$levelData['level_name']."会员，全新特权为您开启";

                }else{
                    $msg_title = '降级通知';
                    $notice_href_keyword = "查看升级规则";
                    $content  = "根据近3个月的累计分数来看，分数不达标，已降级到".$levelData['level_name']."会员 ";

                }

                //供应商升降级图片
                $up_down_img['supplier'] = array(
                    'up'=>[
                        2=> 'https://image.isoubu.com/sysMsg/5a461ed75a374.png',//铜
                        3=> 'https://image.isoubu.com/sysMsg/5a461ee5c5a35.png',//银
                        4=> 'https://image.isoubu.com/sysMsg/5a461ecfd5415.png',//金
                        5=> 'https://image.isoubu.com/sysMsg/5a461eeab7b7d.png',//砖石
                    ],
                    'down'=>[
                        1=>"https://image.isoubu.com/member_msg/downgrade-normal-provider.png",//普通
                        2=>"https://image.isoubu.com/member_msg/downgrade-bronze-provider.png",//铜
                        3=>"https://image.isoubu.com/member_msg/downgrade-silver-provider.png",//银
                        4=>"https://image.isoubu.com/member_msg/downgrade-gold-provider.png",//金牌
                    ]
                );

                //通知图片地址
                if($levelData['level_id'] > $current_score['levelId']){
                    $notice_img_url = $up_down_img['supplier']['up'][$levelData['level_id']];
                }else{
                    $notice_img_url = $up_down_img['supplier']['down'][$levelData['level_id']];
                }

                $info['title']  = $info['msgTitle'] = $msg_title;
                $info['msgContent'] = $info['content'] = $content;
                $info['imgUrl'] = $notice_img_url;
                $info['Url'] = $this->userData->getSetting('user_center_home_page');//会员中心首页
                $info['isRich'] = 1;
                $info['commendUser'] = array();
                $d = [["keyword"=>"#".$notice_href_keyword."#","type"=>20,"id"=>0,"url"=>""]];//跳转到会员中心
                $info['data'] = $d;
                $info['showData'] = array();
                sendInstantMessaging("1", (string)$data['user_id'], json_encode($info));

                ###### 发送等级变动通知结束 ######
            }
            $level_id = $levelData['level_id'];
        }

        return [
            'updateScoreRes' => $updateScoreRes,
            'updateUserRes' => $updateUserRes,
            'UserLevelRec' => $UserLevelRec,
            'level_id' => $level_id
        ];
    }

}