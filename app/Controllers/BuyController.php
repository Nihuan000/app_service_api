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

use App\Models\Data\BuyData;
use App\Models\Data\UserData;
use App\Models\Logic\ElasticsearchLogic;
use App\Models\Logic\ProductLogic;
use Swoft\App;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Task\Task;

/**
 * Class BuyControllerController
 * @Controller()
 * @package App\Controllers
 */
class BuyController{

    /**
     * @Inject()
     * @var BuyData
     */
    protected $buyData;

    /**
     * @Inject()
     * @var UserData
     */
    protected $userData;

    /**
     * 实商匹配采购自动报价
     * @RequestMapping()
     * @param Request $request
     * @return array
     * @throws \Swoft\Task\Exception\TaskException
     */
    public function buy_auto_offer(Request $request): array
    {
        $buy_id = $request->post('buy_id');
        $buy_remark = $request->post('remark');
        $tag_list = $request->post('buy_tag');
        $buy_img_list = $request->post('img_list');
        if(empty($buy_id) || (empty($buy_remark) && empty($tag_list))){
            $code = 0;
            $result = [];
            $msg = '请求参数错误';
        }else{
            write_log(2,$buy_id . '=>' . $buy_remark . ' => ' . $tag_list . ' => ' . $buy_img_list);
            Task::deliver('Offer','Product_Auto_offer',[$buy_id, $buy_remark,$tag_list,$buy_img_list], Task::TYPE_ASYNC);
            $code = 0;
            $result = [];
            $msg = '成功';
        }
        return compact("code","result","msg");
    }
}
