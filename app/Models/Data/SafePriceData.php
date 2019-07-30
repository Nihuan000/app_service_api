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

use App\Models\Dao\SafePriceDao;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\MysqlException;

/**
 * 用户数据类
 * @Bean()
 * @uses      UserData
 * @author    Dailifeng
 */
class SafePriceData
{

    /**
     * @Inject()
     * @var SafePriceDao
     */
    private $SafePriceDao;

    /**
     * @author Dailifeng
     * @param $uid
     * @return mixed
     */
    public function getLastLogInfo($uid)
    {
        return $this->SafePriceDao->getLastLogInfo($uid);
    }

    /**
     * @param $data
     * @return mixed
     * @throws MysqlException
     * @author Dailifeng
     */
    public function addSafePriceLog($data)
    {
        return $this->SafePriceDao->addSafePriceLog($data);
    }
}
