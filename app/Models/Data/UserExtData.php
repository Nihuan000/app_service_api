<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Models\Data;

use App\Models\Dao\UserExtDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Core\ResultInterface;
use Swoft\Db\Exception\MysqlException;

/**
 * 用户扩展信息获取
 * @Bean()
 * @uses      UserExtData
 */
class UserExtData
{
    /**
     * @Inject()
     * @var UserExtDao
     */
    private $userExtDao;

    public function getExtInfo(int $user_id, int $role)
    {
        return $this->userExtDao->getExtInfo($user_id,$role);
    }

    /**
     * 添加钱包记录
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function addWallete(array $data)
    {
        return $this->userExtDao->setUserWallet($data);
    }

    /**
     * 添加收货地址
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function addAddress(array $data)
    {
        return $this->userExtDao->setUserAddress($data);
    }

    /**
     * 添加公司信息
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function addCompany(array $data)
    {
        return $this->userExtDao->setUserCompany($data);
    }

    /**
     * 添加用户积分
     * @param int $role
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function addScore(int $role, array $data)
    {
        return $this->userExtDao->setUserScore($role, $data);
    }

    /**
     * 用户身份修改次数判断
     * @param int $user_id
     * @return ResultInterface
     */
    public function getRoleUpdateCount(int $user_id)
    {
        return $this->userExtDao->getUserChangeLog($user_id);
    }

    /**
     * 获取用户设备号列表
     * @param string $device
     * @return mixed
     */
    public function getUserDevice(string $device)
    {
        return $this->userExtDao->getUsualDevice($device);
    }
}
