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

use App\Models\Logic\ScoreLogic;
use Swoft\App;
use Swoft\Db\Exception\DbException;
use Swoft\Http\Message\Bean\Annotation\Middleware;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use App\Middlewares\ActionVerifyMiddleware;

/**
 * Class ScoreController
 * @Middleware(class=ActionVerifyMiddleware::class)
 * @Controller(prefix="/score")
 * @package App\Controllers
 */
class ScoreController{

    /**
     * 加分操作
     * @param Request $request
     * @return array
     * @throws DbException
     */
    public function increase(Request $request)
    {
        var_dump($request->post());
        $user_id = $request->post('user_id');
        $scenes = $request->post('scenes');
        $extended = $request->post('extended');
        if(empty($user_id) || empty($scenes)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $attr = json_decode($extended,true);
            /* @var ScoreLogic $score_logic */
            $score_logic = App::getBean(ScoreLogic::class);
            $pushRes = $score_logic->user_score_increase($user_id,$scenes,$attr);
            $code = 0;
            switch ($pushRes){
                case 0:
                    $msg = '更新失败';
                    break;

                case 1:
                    $msg = '记录成功';
                    $code = 1;
                    break;

                case -1:
                    $msg = '用户信息为空';
                    break;

                case -2:
                    $msg = '规则不存在';
                    break;

                case -3:
                    $msg = '积分已达上限或条件不满足';
                    break;

                case 404:
                    $msg = '产品不存在或积分已领取';
                    break;

                case 405:
                    $msg = '报价积分记录已存在';
                    break;
            }
            $result = [];
        }
        return compact('code','msg','result');
    }

    /**
     * 减分操作
     * @param Request $request
     * @return array
     * @throws DbException
     */
    public function deduction(Request $request)
    {
        $user_id = $request->post('user_id');
        $scenes = $request->post('scenes');
        $extended = $request->post('extended');
        if(empty($user_id) || empty($scenes)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            $attr = json_decode($extended,true);
            /* @var ScoreLogic $score_logic */
            $score_logic = App::getBean(ScoreLogic::class);
            $pushRes = $score_logic->user_score_deduction($user_id,$scenes,$attr);
            $code = 0;
            switch ($pushRes){
                case 0:
                    $msg = '更新失败';
                    break;

                case 1:
                    $msg = '记录成功';
                    $code = 1;
                    break;

                case -2:
                    $msg = '规则不存在';
                    break;

                case -3:
                    $msg = '不符合积分规则';
                    break;
            }
            $result = [];
        }
        return compact('code','msg','result');
    }

    /**
     * 获取积分
     * @param Request $request
     */
    public function earn_score(Request $request)
    {

    }
}
