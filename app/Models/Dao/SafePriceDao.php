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

use Swoft\Bean\Annotation\Bean;
use Swoft\Core\ResultInterface;
use Swoft\Db\Db;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
use Swoft\Db\Query;

/**
 * 用户数据对象
 * @Bean()
 * @uses SafePriceDao
 * @author Dailifeng
 */
class SafePriceDao
{
    /**
     * 获取最后一条保证金日志
     * @author Dailifeng
     * @param int $user_id
     * @return mixed
     */
    public function getLastLogInfo(int $user_id)
    {
        $sb_safe_price_log = Query::table('sb_safe_price_log')
            ->where('user_id',$user_id)
            ->orderBy('id','DESC')
            ->limit(1)
            ->get()
            ->getResult();
        $sb_safe_price_log = current($sb_safe_price_log);
        return $sb_safe_price_log;
    }


    /**
     * 添加数据到保证金日志
     * @param array $data
     * @return mixed
     * @throws MysqlException
     */
    public function addSafePriceLog(array $data)
    {
        return Query::table('sb_safe_price_log')->insert($data)->getResult();
    }
}
