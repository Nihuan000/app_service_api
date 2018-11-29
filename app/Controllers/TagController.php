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
     */
    public function set_downgrade_tag(Request $request)
    {
        $user_id = $request->post('user_id');
        $set_type = $request->post('set_type');
        $tag_type = $request->post('tag_cate_id');
        $tag_ids = $request->post('tag_ids');
        if(empty($user_id) || empty($tag_type) || empty($tag_type) || empty($tag_ids)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            /* @var TagLogic $tag_logic */
            $tag_logic = App::getBean(TagLogic::class);
            $tag_logic->set_tag_level([
                'user_id' => $user_id,
                'set_type' => $set_type,
                'tag_type' => $tag_type,
                'tag_ids' => $tag_ids,
            ]);
            $code = 200;
            $result = [];
            $msg = '提交成功';
        }
        return compact('code','result','msg');
    }
}