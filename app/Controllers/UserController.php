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
use App\Models\Logic\UserStrengthLogic;
use Swoft\App;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Exception\MysqlException;
use Swoft\Log\Log;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Redis\Redis;

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
     * @Inject("searchRedis")
     * @var Redis
     */
    private $redis;

    /**
     * @Inject("demoRedis")
     * @var Redis
     */
    private $msgRedis;

    private $queue_key = 'msg_queue_list';


    /**
     * 采购商用户成长值操作
     * @author yang
     * @RequestMapping()
     * @param Request $request
     * @return array
     */
    public function user_growth(Request $request): array
    {
        $params = [];
        $params['user_id'] = $request->post('user_id');
        $params['name'] = $request->post('name');//积分规则标识符
        $params['operate_id'] = empty($request->post('operate_id')) ? 0 : $request->post('operate_id');//管理员id
        $params['system'] = empty($request->post('system')) ? 0 : $request->post('system');//手机类型区分
        $params['version'] = empty($request->post('version')) ? 0 : $request->post('version');//手机版本

        $code = 0;
        $result = [];
        $msg = '参数缺失';

        if (!empty($params['user_id']) && !empty($params['name'])){

            $rule = $this->userData->getUserGrowthRule($params['name']);
            $user_info = $this->userData->getUserInfo($params['user_id']);
            $msg = '参数错误';
            if (isset($rule) && isset($user_info)){
                if ($rule['name'] == 'personal_data'){
                    if (empty($params['system']) || empty($params['version'])) return compact("code","result","msg");
                }
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
     * @param Request $request
     * @return array
     * @throws DbException
     */
    public function user_growth_redo(Request $request): array
    {
        //1.查出所有采购商用户
        //2.循环查询用户的  认证状态，发布采购成功数，采纳报价次数,交易成功额度，给卖家的评分数，收到卖家好评数，收到卖家差评数   然后计算总分
        //3.将每个分类的分数生成一条记录
        //4.将总分写入，计算等级
        set_time_limit(0);

        $authentication = $this->userData->getUserGrowthRule('authentication');
        $felease_buy = $this->userData->getUserGrowthRule('felease_buy');
        $adopt_offer = $this->userData->getUserGrowthRule('adopt_offer');
        $transaction_limit = $this->userData->getUserGrowthRule('transaction_limit');
        $active_eval = $this->userData->getUserGrowthRule('active_eval');
        $get_praise = $this->userData->getUserGrowthRule('get_praise');
        $get_bad_eval = $this->userData->getUserGrowthRule('get_bad_eval');
        $personal_data = $this->userData->getUserGrowthRule('personal_data');

        $post_user_id = $request->post('user_id');;
        $limit = 1;
        $user_list = $this->userData->getUserList($post_user_id, $limit);

        foreach ($user_list as $key => $value) {

            $user_id  = $value['userId'];
            $main_product  = $value['mainProduct'];
            $purchaser = $value['certificationType'];//认证状态
            $phone_type = $value['phoneType'];//认证状态
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
            if ($purchaser != 0) {
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

            if ($phone_type==1){
                //安卓
                $user_data_growth = $this->userData->androidUserDate($user_id,$main_product);
            }else{
                //ios
                /* @var UserLogic $user_logic */
                $user_logic = App::getBean(UserLogic::class);
                $user_data_growth = $user_logic->get_completion_rate($user_id, $main_product);
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
        }
        return ['msg'=>'计算成功'];
    }

    /**
     * 实商过期
     * @param Request $request
     * @return array
     * @throws DbException
     */
    public function strength_expired(Request $request)
    {
        $user_id = $request->post('user_id');
        $is_experience = $request->post('is_experience',0);
        if(empty($user_id)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            /* @var UserStrengthLogic $strength_logic */
            $strength_logic = App::getBean(UserStrengthLogic::class);
            $expiredRes = $strength_logic->user_strength_expired([],$user_id,$is_experience);
            if(!empty($expiredRes)){
                $code = 1;
                $result = [];
                $msg = '执行成功';
            }
        }
        return compact('code','msg','result');
    }

    /**
     * 实商到期提醒添加
     * @param Request $request
     * @return array
     * @throws DbException
     */
    public function strength_expire_notice(Request $request)
    {
        $user_id = $request->post('user_id');
        if(empty($user_id)){
            $code = 0;
            $result = [];
            $msg = '参数错误';
        }else{
            /* @var UserStrengthLogic $strength_logic */
            $strength_logic = App::getBean(UserStrengthLogic::class);
            $sendRes = $strength_logic->strength_expire_notice($user_id);
            if(!empty($sendRes)){
                $code = 1;
                $result = [];
                $msg = '执行成功';
            }
        }
        return compact('code','msg','result');
    }

    /**
     * 用户实商变更记录
     * @param Request $request
     * @return array
     * @throws MysqlException
     */
    public function user_strength_record(Request $request)
    {
        $user_id = $request->post('user_id');
        $old_time = $request->post('old_time');
        $end_time = $request->post('end_time');
        $change_type = $request->post('change_type');
        $opt_user = $request->post('opt_user_id');
        if(empty($user_id) || empty($end_time) || !in_array($change_type,[1,2,3,4,5,6,7])){
            $code = 0;
            $result = [];
            $msg = '非法请求';
        }else{
            $record = [
                'user_id' => $user_id,
                'old_end_time' => (int)$old_time,
                'new_end_time' => (int)$end_time,
                'change_type' => $change_type,
                'opt_user_id' => (int)$opt_user
            ];
            /* @var UserLogic $user_logic */
            $user_logic = App::getBean(UserLogic::class);
            $pushRes = $user_logic->strength_history($record);
            if($pushRes == -1){
                $msg = 'REPEAT';
            }
            if($pushRes > 0){
                $msg = 'SUCCESS';
            }
            $code = 1;
            $result = [];
        }
        return compact('code','msg','result');
    }

    /**
     * 实商开通入口
     * @param Request $request
     */
    public function user_strength_open(Request $request)
    {
        //todo 实商开通/续费功能重写
        $user_id = $request->post('user_id');
    }
}
