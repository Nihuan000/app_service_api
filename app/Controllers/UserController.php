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

use App\Models\Logic\UserLogic;
use App\Models\Data\UserData;
use App\Models\Data\BuyData;
use App\Models\Data\OrderData;
use Swoft\App;
use Swoft\Log\Log;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;

/**
 * Class UserControllerController
 * @Controller()
 * @package App\Controllers
 */
class UserController{

    /**
     * @Inject()
     * @var UserData
     */
    protected $userData;

    /**
     * @Inject()
     * @var BuyData
     */
    protected $buyData;

    /**
     * @Inject()
     * @var OrderData
     */
    protected $orderData;

    /**
     * 采购商用户成长值操作
     * @author yang
     * @RequestMapping()
     * @param Request $request
     * @return array
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function user_growth(Request $request): array
    {
        $params = [];
        $params['user_id'] = $request->post('user_id');
        $params['name'] = $request->post('name');//积分规则标识符
        $params['operate_id'] = empty($request->post('operate_id')) ? 0 : $request->post('operate_id');//管理员id

        $code = 0;
        $result = [];
        $msg = '参数缺失';

        if (!empty($params['user_id']) && !empty($params['name'])){

            $rule = $this->userData->getUserGrowthRule($params['name']);
            $user_info = $this->userData->getUserInfo($params['user_id']);
            $msg = '参数错误';
            if (isset($rule) && isset($user_info)){
                /* @var UserLogic $user_logic */
                $user_logic = App::getBean(UserLogic::class);
                $result = $user_logic->growth($params, $rule);
                if ($result){
                    $code = 1;
                    $msg = '成长值更新成功';
                }else{
                    $msg = '成长值更新失败';
                }
            }
        }
        return compact("code","result","msg");
    }

    /**
     * 采购商用户成长值操作
     * @author yang
     * @RequestMapping()
     * @return array
     * @throws \Swoft\Db\Exception\MysqlException
     */
    public function user_growth_redo()
    {
        //1.查出所有采购商用户
        //2.循环查询用户的  认证状态，发布采购成功数，采纳报价次数,交易成功额度，给卖家的评分数，收到卖家好评数，收到卖家差评数   然后计算总分
        //3.将每个分类的分数生成一条记录
        //4.将总分写入，计算等级
        $log = '';
        $user_list = $this->userData->getUserList();

        $authentication = $this->userData->getUserGrowthRule('authentication');
        $felease_buy = $this->userData->getUserGrowthRule('felease_buy');
        $adopt_offer = $this->userData->getUserGrowthRule('adopt_offer');
        $transaction_limit = $this->userData->getUserGrowthRule('transaction_limit');
        $active_eval = $this->userData->getUserGrowthRule('active_eval');
        $get_praise = $this->userData->getUserGrowthRule('get_praise');
        $get_bad_eval = $this->userData->getUserGrowthRule('get_bad_eval');
        $personal_data = $this->userData->getUserGrowthRule('personal_data');

        foreach ($user_list as $key => $value) {

            $user_id  = $value['userId'];
            $main_product  = $value['mainProduct'];
            $purchaser = $value['purchaser'];//认证状态
            $buy_count = $this->buyData->getBuyCount($user_id);//发布采购成功数
            $offer_count = $this->buyData->getOfferCount($user_id);//采纳报价次数
            $total_order_price = $this->orderData->getOrderAllPrice($user_id);//交易成功额度
            $review = $this->userData->getReviewCount($user_id);//给卖家的评分数
            $good_count = $this->userData->getReviewGoodCount($user_id);//收到卖家好评数
            $bad_count = $this->userData->getReviewBadCount($user_id);//收到卖家差评数

            $data = [
                'user_id' => $user_id,
                'add_time' => time(),
                'update_time' => time(),
                'version' => 1,
                'status' => 1
            ];
            $authentication_growth = 0;
            $felease_buy_growth = 0;
            $adopt_offer_growth = 0;
            $transaction_limit_growth = 0;
            $active_eval_growth = 0;
            $get_praise_growth = 0;
            $get_bad_eval_growth = 0;
            $user_data_growth = 0;

            //认证记录
            if ($purchaser == 1) {
                $authentication_growth = $authentication['value'];
                $data['growth_id'] = $authentication['id'];
                $data['growth'] = $authentication_growth;
                $data['name'] = $authentication['name'];
                $data['title'] = $authentication['title'];
                $data['remark'] = $authentication['remark'];
                $this->userData->userGrowthRecordInsert($data);
            }

            //发布采购成功记录
            if (!empty($buy_count)) {
                $felease_buy_growth = $felease_buy['value'] * $buy_count;
                $data['growth_id'] = $felease_buy['id'];
                $data['growth'] = $felease_buy_growth;
                $data['name'] = $felease_buy['name'];
                $data['title'] = '历史' . $felease_buy['title'];
                $data['remark'] = '历史' . $felease_buy['remark'];
                $this->userData->userGrowthRecordInsert($data);
            }

            //采纳报价记录
            if (!empty($offer_count)) {
                $adopt_offer_growth = $adopt_offer['value'] * $offer_count;
                $data['growth_id'] = $adopt_offer['id'];
                $data['growth'] = $adopt_offer_growth;
                $data['name'] = $adopt_offer['name'];
                $data['title'] = '历史' . $adopt_offer['title'];
                $data['remark'] = '历史' . $adopt_offer['remark'];
                $this->userData->userGrowthRecordInsert($data);
            }

            //交易成功额度记录
            if (!empty($total_order_price)) {
                $transaction_limit_growth = intval($total_order_price / 1000);
                $data['growth_id'] = $transaction_limit['id'];
                $data['growth'] = $transaction_limit_growth;
                $data['name'] = $transaction_limit['name'];
                $data['title'] = $transaction_limit['title'];
                $data['remark'] = $transaction_limit['remark'];
                $this->userData->userGrowthRecordInsert($data);
            }

            //给卖家的评分数记录
            if (!empty($review)) {
                $active_eval_growth = $active_eval['value'] * $review;
                $data['growth_id'] = $active_eval['id'];
                $data['growth'] = $active_eval_growth;
                $data['name'] = $active_eval['name'];
                $data['title'] = '历史' . $active_eval['title'];
                $data['remark'] = '历史' . $active_eval['remark'];
                $this->userData->userGrowthRecordInsert($data);
            }

            //收到卖家好评数记录
            if (!empty($good_count)) {
                $get_praise_growth = $get_praise['value'] * $good_count;
                $data['growth_id'] = $get_praise['id'];
                $data['growth'] = $get_praise_growth;
                $data['name'] = $get_praise['name'];
                $data['title'] = '历史' . $get_praise['title'];
                $data['remark'] = '历史' . $get_praise['remark'];
                $this->userData->userGrowthRecordInsert($data);
            }

            //收到卖家差评数记录
            if (!empty($bad_count)) {
                $get_bad_eval_growth = $get_bad_eval['value'] * $bad_count;
                $data['growth_id'] = $get_bad_eval['id'];
                $data['growth'] = $get_bad_eval_growth;
                $data['name'] = $get_bad_eval['name'];
                $data['title'] = '历史' . $get_bad_eval['title'];
                $data['remark'] = '历史' . $get_bad_eval['remark'];
                $this->userData->userGrowthRecordInsert($data);
            }

            //计算资料完善度
            if (!empty($main_product)){
                $user_data_growth += 33;//主营产品
            }

            $purchaser_role = $this->userData->getUserPurchaserRole($user_id);//采购身份
            if (!empty($purchaser_role)){
                $user_data_growth += 34;
            }
            $purchaser_industry = $this->userData->getUserPurchaserIndustry($user_id);//主营行业
            if (!empty($purchaser_industry)){
                $user_data_growth += 33;
            }
            $data['growth_id'] = $personal_data['id'];
            $data['growth'] = $user_data_growth;
            $data['name'] = $personal_data['name'];
            $data['title'] = $personal_data['title'];
            $data['remark'] =  $personal_data['remark'];
            $this->userData->userGrowthRecordInsert($data);


            //计算总分计算等级
            $all_growth = $authentication_growth + $felease_buy_growth + $adopt_offer_growth + $transaction_limit_growth + $active_eval_growth + $get_praise_growth + $get_bad_eval_growth + $user_data_growth;

            $this->userData->userGrowthUpdate($all_growth, $user_id, 1);//更新总分

            //计算等级
            $level = $this->userData->getUserLevelRule($all_growth);
            //更新等级
            $this->userData->userUpdate(['level'=>$level['level_sort']], $user_id);


            $log .=' 用户：'.$user_id.' 总分：'.$all_growth.' 等级：'.$level['level_sort'];
        }

        Log::info($log);
        $code = 1;
        $msg = '分数计算成功';
        return compact("code","msg");
    }

}
