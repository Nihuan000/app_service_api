<?php

namespace App\Models\Logic;

use App\Models\Data\UserData;
use Swoft\App;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Redis\Redis;

/**
 * 实商处理类
 * @Bean()
 * @uses      UserStrengthLogic
 */
class UserStrengthLogic
{
    /**
     *
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
     * 保证金体验记录id
     * @var int
     */
    private $safe_experience_id = 1;

    /**
     * 用户实商过期处理
     * @param array $strength_info
     * @param int $user_id
     * @param int $is_experience
     * @return int
     * @throws DbException
     */
    public function user_strength_expired(array $strength_info = [], int $user_id = 0,int $is_experience = 0)
    {
        $now_time = time();
        $code = 0;
        if(empty($strength_info) && $user_id == 0){
            $code = -1;
        }else{

            //调用接口请求，判断user_id获取实商信息
            if($user_id > 0){
                $user_strength = $this->userData->getUserStrengthInfo($user_id);
                if(!empty($user_strength)){
                    $strength_info = [
                        'id' => $user_strength['id'],
                        'user_id' => $user_strength['user_id'],
                        'pay_for_open' => $user_strength['pay_for_open'],
                        'end_time' => $user_strength['end_time'],
                    ];
                }
            }

            if(!empty($strength_info)){
                //付费实商未到期,不予处理
                if($strength_info['end_time'] > $now_time && $strength_info['pay_for_open'] == 1){
                    $code = -2;
                    return $code;
                }
                Db::beginTransaction();
                $remark = '实商到期';
                $experienceRes = true;
                //判断是否体验实商
                if($strength_info['pay_for_open'] == 0){
                    //体验记录过期
                    $experience = $this->userData->get_strength_experience_info($strength_info['user_id']);
                    if(!empty($experience)){
                        if($experience['experience_id'] == $this->safe_experience_id){
                            $remark = "提取保证金,实商体验取消";
                        }else{
                            if($strength_info['end_time'] > $now_time && $is_experience == 0){
                                $code = -2; //体验未到期
                                return $code;
                            }
                            $experInfo = $this->userData->get_experience_info($experience['experience_id']);
                            if(!empty($experInfo)){
                                $remark = $experInfo['experience_name'] . '到期';
                            }
                        }
                        $real_experience_day = (strtotime(date('Y-m-d',$now_time)) - strtotime(date('Y-m-d',$experience['add_time'])))/(3600*24);//实际体验的天数

                        //执行体验过期
                        $data = [
                            'remark' => $remark,
                            'real_experience_day' => $real_experience_day
                        ];
                        $experienceRes = $this->userData->strength_receive_expired($experience['id'],$data);
                    }
                }

                //实商记录过期
                $strengthParams = [
                    'end_time' => $now_time,
                    'is_expire' => 1,
                    'update_time' => $now_time,
                    'remark' => $remark
                ];
                $strengthRes = $this->userData->userStrengthPlus($strength_info['user_id'],$strength_info['id'],$strengthParams);
                if($experienceRes && $strengthRes)
                {
                    Db::commit();
                    $code = 1;
                }else{
                    Db::rollback();
                    $code = -3;
                }

                if($code == 1){
                    //用户积分扣除
                    /* @var ScoreLogic $score_logic */
                    $score_logic = App::getBean(ScoreLogic::class);
                    $score_logic->user_score_deduction($strength_info['user_id'],'seller_user_strength_experience_expire',['id' => $strength_info['id']]);


                    //发送实商到期提醒
                    if($strength_info['pay_for_open'] == 1){
                        $this->strength_expire_notice($strength_info['user_id']);
                    }
                }
            }
        }
        return $code;
    }

    /**
     * @param int $user_id
     * @return array
     * @throws DbException
     */
    public function strength_expire_notice(int $user_id)
    {
        $notice_history_key = 'over_strength_history_' . date('Y'); //提示历史记录
        $user_ids = [];
        $config = \Swoft::getBean('config');
        $sys_msg = $config->get('sysMsg');
        $receive_status = 0;
        $test_list = $this->userData->getTesters();
        if(in_array($user_id, $test_list) || $this->userData->getSetting('strength_over_switch') == 1){
            $receive_status = 1;
        }
        if($receive_status == 1){
            $history_record = $this->redis->sIsMember($notice_history_key,(string)$user_id);
            if($history_record == 0){
                //发送系统消息
                ################## 消息基本信息开始 #######################
                $extra = $sys_msg;
                $extra['title'] = '实商已到期';
                $extra['msgContent'] = "您的实力商家权限已到期，\n点击重新开通";
                ################## 消息基本信息结束 #######################

                ################## 消息扩展字段开始 #######################
                $extraData['keyword'] = '#点击重新开通#';
                $extraData['type'] = 18;
                $extraData['url'] = $this->userData->getSetting('user_strength_url');
                ################## 消息扩展字段结束 #######################

                $extra['data'] = [$extraData];
                $extra['content'] = "您的实力商家权限已到期，#点击重新开通#";
                $notice['extra'] = $extra;
                $this->redis->sAdd($notice_history_key, (string)$user_id);
                sendInstantMessaging('1', (string)$user_id, json_encode($notice['extra']));
                $user_ids[] = $user_id;
            }else{
                write_log(2,$user_id . '实商到期推送记录已存在');
            }
        }else{
            write_log(2,$user_id . '存在已开通记录，不再提醒');
        }
        if(!empty($user_ids)){
            write_log(2,json_encode($user_ids));
        }
        return $user_ids;
    }
}
