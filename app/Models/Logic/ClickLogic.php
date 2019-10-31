<?php
/**
 * Created by PhpStorm.
 * User: nihuan
 * Date: 2019/10/28
 * Time: 下午4:45
 * Desc: 访问记录写入
 */

namespace App\Models\Logic;

use App\Models\Data\BuyData;
use App\Models\Data\ProductData;
use App\Models\Data\UserData;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Db\Exception\MysqlException;

/**
 * Class ClickLogic
 * @Bean()
 * @uses  ClickLogic
 * @package App\Models\Logic
 */
class ClickLogic
{
    /**
     * @Inject()
     * @var ProductData
     */
    private $prodocutData;

    /**
     * @Inject()
     * @var UserData
     */
    private $userData;

    /**
     * @Inject()
     * @var BuyData
     */
    private $buyData;

    /**
     * 点击事件记录
     * @param int $type
     * @param array $data
     * @return bool
     * @throws MysqlException
     */
    public function event_record(int $type, array $data)
    {
        $record_res = false;
        switch ($type)
        {
            //产品记录
            case 1:
                $product['user_id'] = (int)$data['user_id'];
                $product['pro_id'] = (int)$data['pro_id'];
                $product['r_time'] = (int)$data['r_time'];
                $product['from_type'] = (int)$data['from_type'];
                $product['scene'] = (int)$data['scene'];
                $product['business'] = (string)$data['business'];
                $product['is_filter'] = (int)$data['is_filter'];
                $product['request_id'] = (string)$data['request_id'];
                //添加产品记录/产品点击量
                $product_res = $this->prodocutData->setProductRecordLog($product);

                //添加访客记录
                $shop['user_id'] = (int)$data['user_id'];
                $shop['visited_user_id'] = (int)$data['shop_id'];
                $shop['r_time'] = (int)$data['r_time'];
                $shop['from_type'] = (int)$data['from_type'];
                $shop['scene'] = (int)$data['scene'];
                $shop['thing_id'] = (int)$data['pro_id'];
                $shop['thing_type'] = 1;
                $visit_res = $this->userData->setUserVisitLog($shop);
                if($visit_res && $product_res){
                    $record_res = true;
                }
                break;

            //采购记录
            case 2:
                //添加采购记录/更新采购点击量
                $buy['user_id'] = (int)$data['user_id'];
                $buy['buy_id'] = (int)$data['buy_id'];
                $buy['r_time'] = (int)$data['r_time'];
                $buy['scene'] = (int)$data['scene'];
                $buy['is_filter'] = (int)$data['is_filter'];
                $buy['request_id'] = (string)$data['request_id'];
                $buy['from_type'] = (int)$data['from_type'];

                $record_res = $this->buyData->setBuyRecordLog($buy);
                break;

            //店铺记录
            case 3:
                //添加店铺访问记录/店铺点击量
                $shop['user_id'] = (int)$data['user_id'];
                $shop['shop_id'] = (int)$data['shop_id'];
                $shop['r_time'] = (int)$data['r_time'];
                $shop['from_type'] = (int)$data['from_type'];
                $shop['scene'] = (int)$data['scene'];
                $shop_res = $this->userData->setVisitShopLog($shop);

                //添加访客记录
                $shop['user_id'] = (int)$data['user_id'];
                $shop['visited_user_id'] = (int)$data['shop_id'];
                $shop['r_time'] = (int)$data['r_time'];
                $shop['from_type'] = (int)$data['from_type'];
                $shop['scene'] = (int)$data['scene'];
                $shop['thing_id'] = (int)$data['shop_id'];
                $shop['thing_type'] = 2;
                $visit_res = $this->userData->setUserVisitLog($shop);
                if($shop_res && $visit_res){
                    $record_res = true;
                }
                break;
        }

        return $record_res;
    }
}
