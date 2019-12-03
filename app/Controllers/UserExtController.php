<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Controllers;

use Swoft\Http\Message\Bean\Annotation\Middleware;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Task\Exception\TaskException;
use App\Middlewares\ActionVerifyMiddleware;
use Swoft\Task\Task;

/**
 * 用户扩展信息
 * Class UserExtController
 * @Controller(prefix="/userExt")
 * @Middleware(class=ActionVerifyMiddleware::class)
 * @package App\Controllers
 */
class UserExtController{

    /**
     * @param Request $request
     * @return array
     * @throws TaskException
     */
    public function completion(Request $request): array
    {
        $user_id = $request->post('user_id'); //用户id
        $role = $request->post('role'); //修改后的身份 1:采购 2：供应
        if(empty($user_id) || empty($role)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            Task::deliver('UserExtCompletion', 'work',[$user_id, $role], Task::TYPE_ASYNC);
            $code = 200;
            $msg = 'ok，执行中...';
            $result = [];
        }
        return compact('code','msg','result');
    }
}
