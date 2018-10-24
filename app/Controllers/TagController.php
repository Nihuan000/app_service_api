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
     * @throws \Swoft\Db\Exception\DbException
     */
    public function user_tag_refresh(Request $request)
    {
        $user_id = $request->post('user_id');
        /* @var TagLogic $tag_logic */
        $tag_logic = App::getBean(TagLogic::class);
        $tag_logic->event_analysis([
            'user_id' => $user_id,
        ]);
    }
}