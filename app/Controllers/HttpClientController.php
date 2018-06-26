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

use Swoft\Bean\Annotation\Inject;
use Swoft\HttpClient\Client;
use Swoft\Redis\Redis;
use Swoft\Db\Db;
use Swoft\Http\Server\Bean\Annotation\Controller;

/**
 * @Controller(prefix="/httpClient")
 */
class HttpClientController
{
    /**
     * @Inject()
     * @var Redis
     */
    private $redis;
    /**
     * @return array
     * @throws \Swoft\HttpClient\Exception\RuntimeException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function request(): array
    {
        $client = new Client();
        $result = $client->get('http://www.swoft.org')->getResult();
        $result2 = $client->get('http://www.swoft.org')->getResponse()->getBody()->getContents();
        return compact('result', 'result2');
    }


    /**
     * @author Nihuan
     * @throws \Swoft\Db\Exception\DbException
     */
    public function task()
    {
        $industry = 'industry_';
        $seller_event = 'seller_';
        $date = date('Y-m-d');
        $date_time_format = time();
        $redisRes = $this->redis->hGet(INDUSTRY_NEWS_TIME,'industry');
        $last_ansy_time = $redisRes == 0  ? strtotime($date) : (int)$redisRes;
        //采购信息获取,包括新发布/已找到
        $buyResult = Db::query("select t.user_id,t.status,t.is_audit,t.add_time,t.alter_time,sb_user.name,sb_user.city FROM sb_buy as t LEFT JOIN sb_user ON sb_user.user_id = t.user_id WHERE t.alter_time >= ?",[(int)$last_ansy_time])->getResult();
        if(!empty($buyResult)){
            foreach ($buyResult as $item) {
                $is_valid = 0;
                if($item['is_audit'] == 1 && $item['status'] == 0){
                    $is_valid = 1;
                    $data['related_msg'] = '发布了1条求购订单';
                }elseif($item['is_audit'] == 0 && $item['status'] == 1){
                    $is_valid = 1;
                    $data['related_msg'] = '求购订单已找到';
                }
                if($is_valid == 1){
                    $data['city'] = $item['city'];
                    $data['user_name'] = $this->substr_cut($item['name']);
                    $data['alter_time'] = $item['alter_time'];
                    $score = $data['alter_time'];
                    $this->redis->zAdd($industry . $date, $score, json_encode($data));
                    $this->redis->expire($industry . $date,172800);
                }
            }
        }
        //采购商/供应商订单获取
        $OrderRes = Db::query("select add_time,buyer_name,seller_id,seller_name,city FROM sb_order WHERE add_time >= ?", [(int)$last_ansy_time]);
        if(!empty($OrderRes)){
            foreach ($OrderRes as $Order) {
                $order['city'] = $Order['city'];
                $order['user_name'] = $this->substr_cut($Order['buyer_name']);
                $order['related_msg'] = '刚买入了1笔订单';
                $order['alter_time'] = $Order['add_time'];
                $score = $order['alter_time'];
                $this->redis->zAdd($industry . $date,$score,json_encode($order));
                $this->redis->expire($industry . $date,172800);

                $sellerRes = Db::query("select name,city FROM sb_user WHERE user_id = ?", [(int)$Order['seller_id']]);
                if(!empty($sellerRes)){
                    foreach ($sellerRes as $seller) {
                        $order['city'] = $seller['city'];
                        $order['user_name'] = $this->substr_cut($seller['name']);
                        $order['related_msg'] = '刚售出1笔订单';
                        $this->redis->zAdd($seller_event . $date,$score,json_encode($order));
                        $this->redis->expire($seller_event . $date,172800);

                    }
                }
            }
        }

        //供应商报价获取
        $offerRes = Db::query("select t.user_id,sb_buy.amount,sb_buy.unit,t.status,t.offer_time,sb_user.name,sb_user.city FROM sb_offer t LEFT JOIN sb_user ON sb_user.user_id = t.user_id LEFT JOIN sb_buy ON sb_buy.buy_id = t.buy_id WHERE offer_time >= ?", [(int)$last_ansy_time]);
        if(!empty($offerRes)){
            foreach ($offerRes as $offerRe) {
                $offer['city'] = $offerRe['city'];
                $offer['user_name'] = $this->substr_cut($offerRe['name']);
                $offer['related_msg'] = '已报价接单' . $offerRe['amount'] . $offerRe['unit'];
                $offer['alter_time'] = $offerRe['offer_time'];
                $score = $offer['alter_time'];
                $this->redis->zAdd($seller_event . $date,$score,json_encode($offer));
                $this->redis->expire($seller_event . $date,172800);

            }
        }

        //供应商保证金动态获取
        $safeRes = Db::query("select u.city,u.name,t.user_id,t.pay_time FROM sb_safe_price AS t RIGHT JOIN sb_user AS u ON u.user_id = t.user_id WHERE t.pay_time >= ?", [(int)$last_ansy_time]);
        if(!empty($safeRes)){
            foreach ($safeRes as $safeRe) {
                $safe['city'] = $safeRe['city'];
                $safe['user_name'] = $this->substr_cut($safeRe['name']);
                $safe['related_msg'] = '缴存了保证金';
                $safe['alter_time'] = $safeRe['pay_time'];
                $score = $safe['alter_time'];
                $this->redis->zAdd($seller_event . $date,$score,json_encode($safe));
                $this->redis->expire($seller_event . $date,172800);
            }
        }

        //供应商实力商家动态获取
        $strengthRes = Db::query("select u.city,u.name,t.user_id,t.start_time FROM sb_user_strength AS t RIGHT JOIN sb_user AS u ON u.user_id = t.user_id WHERE t.start_time >= ?", [(int)$last_ansy_time]);
        if(!empty($strengthRes)){
            foreach ($strengthRes as $strengthItem) {
                $strength['city'] = $strengthItem['city'];
                $strength['user_name'] = $this->substr_cut($strengthItem['name']);
                $strength['related_msg'] = '开通了实力商家';
                $strength['alter_time'] = $strengthItem['start_time'];
                $score = $strength['alter_time'];
                $this->redis->zAdd($seller_event . $date,$score,json_encode($strength));
                $this->redis->expire($seller_event . $date,172800);
            }
        }
        $this->redis->hSet(INDUSTRY_NEWS_TIME,'industry',$date_time_format);
    }
}