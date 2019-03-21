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
    public function user_level(Request $request): array
    {
        $user_id = $request->post('user_id');
        $name = $request->post('name');//积分规则标识符
        $operate_id = empty($request->post('operate_id')) ? 0 : $request->post('operate_id');//管理员id

        $code = 0;
        $result = [];
        $msg = '参数缺失';

        if (!empty($user_id) && !empty($name)){
            $rule = $this->userData->getUserGrowthRule($name);
            $user_info = $this->userData->getUserInfo($user_id);
            $msg = '参数错误';
            if (isset($rule) && isset($user_info)){
                $data = [
                    'user_id' => $user_id,
                    'growth_id' => $rule['id'],
                    'growth' => $rule['value'],
                    'name' => $name,
                    'title' => $rule['title'],
                    'add_time' => time(),
                    'update_time' => time(),
                    'remark' => $rule['remark'],
                    'version' => 1,
                    'status' => 1,
                    'operate_id' => $operate_id,
                ];
                $this->userData->userGrowthRecordInsert($data);//增加记录
                $this->userData->userGrowthUpdate((int)$rule['value'], $user_id);//更新成长值
                $code = 1;
                $msg = '成长值记录成功';
            }
        }
        return compact("code","result","msg");
    }

}
