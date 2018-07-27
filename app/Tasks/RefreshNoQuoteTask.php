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
     * 热门推送标签统计,每小时执行一次
     * @author Nihuan
     * @Scheduled(cron="0 0 * * * *")
     */
    public function refreshTask()
    {
        $prev_time = strtotime('-8 hour');
        $last_time = strtotime('-9 hour');
        $refresh_prev = strtotime('-30 minute');
        $now_time = date('Y-m-d H:i:s');
        $buyRes = Buy::findAll([
            ['add_time','>',$last_time],
            ['add_time','<=',$prev_time],
            'is_audit' => 0,
            ['refresh_time','>=',$refresh_prev]
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
    }
}