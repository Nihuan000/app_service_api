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
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Task\Exception\TaskException;
use Swoft\Task\Task;

/**
 * Class VisitController
 * @Controller(prefix="/visit")
 * @Middleware(class=ActionVerifyMiddleware::class)
 * @package App\Controllers
 */
class VisitController{
    /**
     * @RequestMapping()
     * @param Request $request
     * @return array
     * @throws TaskException
     */
    public function record(Request $request): array
    {
        $record_type = (int)$request->post('type');
        $data = (array)$request->post('data');
        if(empty($record_type) || empty($data)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            //访问记录任务投递
            $cacheRes = Task::deliver('VisitRecord', 'syncVisitTask',[$record_type, $data], Task::TYPE_ASYNC);
            if($cacheRes){
                $code = 1;
                $result = [];
                $msg = '投递成功';
            }
        }
        return compact('code','msg','result');
    }
}
