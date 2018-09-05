<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 18-7-25
 * Time: 下午5:19
 * Desc: 发布8小时无报价采购刷新
 */

namespace App\Tasks;

use App\Models\Data\BuyAttributeData;
use App\Models\Entity\Buy;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Swoft\Bean\Annotation\Inject;
use Swoft\Task\Bean\Annotation\Scheduled;
use Swoft\Task\Bean\Annotation\Task;

/**
 * RefreshNoQuote task
 *
 * @Task("RefreshNoQuote")
 */
class RefreshNoQuoteTask
{

    /**
     * @Inject()
     * @var BuyAttributeData
     */
    private $buyAttrData;

    /**
     * 8小时无报价采购刷新
     * @author Nihuan
     * @Scheduled(cron="0 0 * * * *")
     */
    public function refreshTask()
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
                $attr = $this->buyAttrData->getByBid($buy['buyId']);
                if($attr['offerCount'] == 0){
                    $buy_ids[] = $buy['buyId'];
                }
            }
            echo "[$now_time] 共刷新采购条数:" . count($buy_ids) . PHP_EOL;
            if(!empty($buy_ids)){
                Buy::updateAll(['refresh_time' => time(),'alter_time' => time()],['buy_id' => $buy_ids])->getResult();
            }
        }
        return ['无报价采购刷新'];
    }
}