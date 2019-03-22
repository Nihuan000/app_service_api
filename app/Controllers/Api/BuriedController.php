<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-6-21
 * Time: 下午3:43
 */

namespace App\Controllers\Api;

use App\Models\Logic\BuriedLogic;
use Swoft\App;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Message\Server\Request;


/**
 * @Controller(prefix="/api/buried")
 * Class BuriedController
 * @package App\Controllers\Api
 */
class BuriedController
{

    /**
     * @author Nihuan
     * @RequestMapping()
     * @param Request $request
     */
    public function set_buried(Request $request)
    {
        //事件名
        $event = $request->post('point_name');
        //用户id
        $distinct_id = $request->post('distinct_id');
        //埋点属性
        $properties = $request->post('properties');
        $event_split = explode('_',$event);
        /* @var BuriedLogic $buried_logic */
        $buried_logic = App::getBean(BuriedLogic::class);
        $buried_logic->event_analysis([
            'event' => $event_split,
            'user_id' => $distinct_id,
            'properties' => json_decode($properties,true)
        ]);
    }

    /**
     * 展示状态记录
     * @param Request $request
     */
    public function display_buried(Request $request)
    {
        $buy_id = $request->post('buy_id');
        $buy_status = $request->post('buy_status');
        $operation_time = $request->post('operation_time');
        if(!empty($buy_id) && !empty($buy_status)){
            /* @var BuriedLogic $buried_logic */
            $buried_logic = App::getBean(BuriedLogic::class);
            $buried_logic->buy_buried([
                'buy_id' => $buy_id,
                'buy_status' => $buy_status,
                'time' => $operation_time
            ]);
        }
    }
}