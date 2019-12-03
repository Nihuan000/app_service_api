<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Dao;

use App\Models\Entity\User;
use Swoft\Bean\Annotation\Bean;
use Swoft\Core\ResultInterface;
use Swoft\Db\Exception\MysqlException;
use Swoft\Db\Query;

/**
 *
 * 用户扩展信息
 * @Bean()
 * @uses      UserExtDao
 */
class UserExtDao
{
    /**
     * 用户扩展信息获取
     * @param int $user_id
     * @param int $role
     * @return array
     */
    public function getExtInfo(int $user_id, int $role)
    {
        $user_info = User::findById($user_id)->getResult();
        //钱包判断
        $user_wallet = Query::table('sb_order_wallet')->where('user_id',$user_id)->limit(1)->get(['wid'])->getResult();
        //默认地址
        $user_address = Query::table('sb_order_address')->where('user_id',$user_id)->limit(1)->get(['address_id'])->getResult();
        //公司信息
        $user_company = Query::table('sb_user_company')->where('user_id',$user_id)->limit(1)->get(['com_id'])->getResult();
        //等级/积分日志
        $user_score = [];
        $user_subscription_tag = [];
        if($role == 1){
            $user_score = Query::table('sb_user_growth')->where('user_id',$user_id)->limit(1)->get(['id'])->getResult();
        }else if($role == 2){
            $user_score = Query::table('sb_user_score')->where('user_id',$user_id)->limit(1)->get(['id'])->getResult();
            //主营订阅
            $user_subscription_tag = Query::table('sb_user_subscription_tag')->where('user_id',$user_id)->limit(1)->get(['user_tag_id'])->getResult();
        }

        return [
            'user_info' => $user_info,
            'order_wallet' => $user_wallet,
            'user_address' => $user_address,
            'user_company' => $user_company,
            'user_score' => $user_score,
            'user_subscription_tag' => $user_subscription_tag,
        ];
    }

    /**
     * 添加用户钱包记录
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function setUserWallet(array $data)
    {
        return Query::table('sb_order_wallet')->insert($data)->getResult();
    }

    /**
     * 添加用户收货地址
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function setUserAddress(array $data)
    {
        return Query::table('sb_order_address')->insert($data)->getResult();
    }

    /**
     * 添加用户公司信息
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function setUserCompany(array $data)
    {
        return Query::table('sb_user_company')->insert($data)->getResult();
    }

    /**
     * 用户积分记录添加
     * @param int $role
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function setUserScore(int $role, array $data)
    {
        if($role == 1){
            return Query::table('sb_user_growth')->insert($data)->getResult();
        }else{
            return Query::table('sb_user_score')->insert($data)->getResult();
        }
    }

    /**
     * 身份修改记录统计
     * @param int $user_id
     * @return ResultInterface
     */
    public function getUserChangeLog(int $user_id)
    {
        $countRes = Query::table('sb_user_update_log')
            ->where('user_id',$user_id)
            ->where('table_name','user')
            ->where('key','role')
            ->where('new_value',2)
            ->count()
            ->getResult();
        $count = empty($countRes) ? 0 : $countRes['count'];
        return $count;
    }

    /**
     * 已领取设备号判断
     * @param string $device
     * @return mixed
     */
    public function getUsualDevice(string $device)
    {
        $receive_count = Query::table('sb_user_strength_experience_receive')->where('device',$device)->count()->getResult();
        return $receive_count;
    }
}
