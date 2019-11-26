<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Tasks;

use App\Models\Data\ProductData;
use App\Models\Logic\ProductLogic;
use Swoft\Bean\Annotation\Inject;
use Swoft\Log\Log;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * Class ProductTask - define some tasks
 *
 * @Task("Product")
 * @package App\Tasks
 */
class ProductTask{

    /**
     * @Inject()
     * @var ProductLogic
     */
    protected $productLogic;

    /**
     * @Inject()
     * @var ProductData
     */
    protected $productData;

    /**
     * 自动重置图片尺寸(目前获取不到尺寸无法标识，暂时不启用)
     */
    public function autoResizeImgTask()
    {
        $res_list = [];
        Log::info('重置产品图片尺寸开始');
        $img_list = $this->productData->getNoSizeImg();
        if(!empty($img_list)){
            foreach ($img_list as $item) {
                $res_list += $this->productLogic->update_pro_img_size($item);
            }
        }
        Log::info(json_encode($res_list));
        Log::info('重置产品图片尺寸结束');
        return '自动获取产品图片尺寸';
    }

    /**
     * 异步调用图片尺寸重置任务
     * @param array $data
     * @return string
     */
    public function imgResizeTask(array $data)
    {
        $queue = json_encode($data);
        Log::info('产品图片任务:' . $queue);
        $resizeRes = $this->productLogic->resize_pro_img((int)$data['pro_id']);
        $msg = '执行结果:';
        if(!empty($resizeRes)){
            foreach ($resizeRes as $id => $resizeRe) {
                switch ($resizeRe){
                    case -1:
                        $msg .= $id . '->oss信息获取失败' . PHP_EOL;
                        break;

                    case -2:
                        $msg .= $id . '->图片地址不符' . PHP_EOL;
                        break;

                    default:
                        $msg .= $id . '->执行成功';
                }
            }
        }
        write_log(2, $msg);
        Log::info('产品图片任务结束:' . $resizeRes);
        return '产品图片尺寸获取';
    }
}
