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

use App\Models\Logic\UserLogic;
use App\Models\Data\UserData;
use Swoft\App;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;

/**
 * Class UserControllerController
 * @Controller()
 * @package App\Controllers
 */
class UserController{

    /**
     * @Inject()
     * @var UserData
     */
    protected $userData;

    /**
     * 采购商用户成长值操作
     * @author yang
     * @RequestMapping()
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function user_growth(Request $request): array
    {
        $params = [];
        $params['user_id'] = $request->post('user_id');
        $params['name'] = $request->post('name');//积分规则标识符
        $params['operate_id'] = empty($request->post('operate_id')) ? 0 : $request->post('operate_id');//管理员id

        $code = 0;
        $result = [];
        $msg = '参数缺失';

        if (!empty($params['user_id']) && !empty($params['name'])){

            $rule = $this->userData->getUserGrowthRule($params['name']);
            $user_info = $this->userData->getUserInfo($params['user_id']);
            $msg = '参数错误';
            if (isset($rule) && isset($user_info)){
                /* @var UserLogic $user_logic */
                $user_logic = App::getBean(UserLogic::class);
                $result = $user_logic->growth($params, $rule);
                if ($result){
                    $code = 1;
                    $msg = '成长值更新成功';
                }else{
                    $msg = '成长值更新失败';
                }
            }
        }
        return compact("code","result","msg");
    }

}
