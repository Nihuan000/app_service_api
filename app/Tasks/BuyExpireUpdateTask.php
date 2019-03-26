<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-9-4
 * Time: 下午3:31
 * Desc: 过期采购状态修改
 */

namespace App\Tasks;
use App\Models\Data\ProductData;
use App\Models\Entity\Buy;
use Swoft\Bean\Annotation\Inject;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * BuyExpireUpdate task
 *
 * @Task("BuyExpireUpdate")
 */
class BuyExpireUpdateTask
{

    /**
     * @Inject()
     * @var ProductData
     */
    protected $productData;
    /**
     * 过期采购状态修改
     * @author Nihuan
     * @Scheduled(cron="0 * * * * *")
     */
    public function expireBuyTask()
    {
        $time = time();
        $now_time = date('Y-m-d H:i:s');
        $buyRes = Buy::findAll([
            ['expire_time','<=',$time],
            ['expire_time','>',0],
            'is_audit' => 0,
            'del_status' => 1,
            'status' => 0
        ],
            ['fields' => ['buy_id']])->getResult();
        if(!empty($buyRes)){
            $buy_ids= [];
            foreach ($buyRes as $buy) {
                $buy_ids[] = $buy['buyId'];
            }
            echo "[$now_time] 采购:" . json_encode($buy_ids) . '已过期' . PHP_EOL;
            if(!empty($buy_ids)){
                Buy::updateAll(['alter_time' => time(), 'status' => 2],['buy_id' => $buy_ids])->getResult();
            }
        }
        sleep(1);
        return ['过期采购状态修改'];
    }

    /**
     * 过期推广产品状态修改
     * @return array
     * @Scheduled(cron="30 * * * * *")
     */
    public function expireSearchKeyword()
    {
        $expire_ids = [];
        $expire_record = $this->productData->getExpireKeyPro();
        if(!empty($expire_record)){
            foreach ($expire_record as $item) {
                $expire_ids[] = $item['id'];
            }
            $params = [
                'ids' => $expire_ids,
                'status' => 0
            ];

            $this->productData->updateExpirePro($params,['status' => 1]);
        }
        return [$expire_ids];
    }
}