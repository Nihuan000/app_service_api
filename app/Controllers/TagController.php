<?php
/**
 * Created by PhpStorm.
 * Date: 18-10-22
 * Time: 上午10:49
 */

namespace App\Controllers;

use App\Models\Logic\TagLogic;
use Swoft\App;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Message\Server\Request;

/**
 * @Controller()
 * Class TagController
 */
class TagController
{

    /**
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function user_tag_refresh(Request $request)
    {
        $user_id = $request->post('user_id');
        if(empty($user_id)){
            $code = 0;
            $result = [];
            $msg = '请求参数错误: user_id';
        }else{
            /* @var TagLogic $tag_logic */
            $tag_logic = App::getBean(TagLogic::class);
            $tag_logic->event_analysis([
                'user_id' => $user_id,
            ]);
            $code = 200;
            $result = [];
            $msg = '刷新成功';
        }
        return compact("code","result","msg");
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\DbException
     */
    public function recommend_reg_supplier(Request $request)
    {
        $user_id = $request->post('user_id');
        if(empty($user_id)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            /* @var TagLogic $tag_logic */
            $tag_logic = App::getBean(TagLogic::class);
            $tag_logic->new_reg_recommend($user_id);
            $code = 200;
            $result = [];
            $msg = '提交成功';
        }
        return compact('code','result','msg');
    }
}