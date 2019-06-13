<?php
/**
 * This file is part of Swoft.
 * 用户积分模块
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Controllers;

use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Server\Bean\Annotation\RequestMethod;
use Swoft\Redis\Redis;

/**
 * Class ScoreController
 * @Controller(prefix="/Score")
 * @package App\Controllers
 */
class ScoreController{

    /**
     * 加分操作
     * @param Request $request
     */
    public function increase(Request $request)
    {

    }

    /**
     * 减分操作
     * @param Request $request
     */
    public function deduction(Request $request)
    {

    }

    /**
     * 获取积分
     * @param Request $request
     */
    public function earn_score(Request $request)
    {

    }
}
