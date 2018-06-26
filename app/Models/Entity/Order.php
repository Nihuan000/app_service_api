<?php
namespace App\Models\Entity;

use Swoft\Db\Model;
use Swoft\Db\Bean\Annotation\Column;
use Swoft\Db\Bean\Annotation\Entity;
use Swoft\Db\Bean\Annotation\Id;
use Swoft\Db\Bean\Annotation\Required;
use Swoft\Db\Bean\Annotation\Table;
use Swoft\Db\Types;

/**
 * @Entity()
 * @Table(name="sb_order")
 * @uses      Order
 */
class Order extends Model
{
    /**
     * @var int $orderId 
     * @Id()
     * @Column(name="order_id", type="integer")
     */
    private $orderId;

    /**
     * @var string $orderNum 订单编号
     * @Column(name="order_num", type="string", length=32, default="'0'")
     */
    private $orderNum;

    /**
     * @var float $totalOrderPrice 总价
     * @Column(name="total_order_price", type="double", default=0)
     */
    private $totalOrderPrice;

    /**
     * @var float $yunfei 运费
     * @Column(name="yunfei", type="double", default=0)
     */
    private $yunfei;

    /**
     * @var int $ruleId 优惠id
     * @Column(name="rule_id", type="integer", default=0)
     */
    private $ruleId;

    /**
     * @var int $ruleType 购买类型1:下单 2:精品 3：产品 4:开单 5:购物车
     * @Column(name="rule_type", type="tinyint", default=0)
     */
    private $ruleType;

    /**
     * @var int $orderSource 下单来源: 1:app首页一键下单 2:app首页一键开单 3:聊天下单 4:聊天开单 5:立即购买 6:购物车下单 7:收到报价下单 8:微信聊天下单 9:微信聊天开单 10:微信商品购买 11:微信购物车下单 12:微信报价下单 13:再来一单
     * @Column(name="order_source", type="tinyint", default=0)
     */
    private $orderSource;

    /**
     * @var float $rulePrice 优惠的金额
     * @Column(name="rule_price", type="double", default=0)
     */
    private $rulePrice;

    /**
     * @var int $couponId 优惠券id
     * @Column(name="coupon_id", type="integer", default=0)
     */
    private $couponId;

    /**
     * @var float $couponPrice 优惠券优惠金额
     * @Column(name="coupon_price", type="float", default=0)
     */
    private $couponPrice;

    /**
     * @var int $fromType 订单来源 0、APP 1、安卓 2、IOS 3、微信 99:批量下单 98:web色卡批量购买 9、微信(接口合并后新增)
     * @Column(name="from_type", type="tinyint", default=0)
     */
    private $fromType;

    /**
     * @var int $orderFrom 订单发起者：0，买家 1，卖家
     * @Column(name="order_from", type="tinyint", default=0)
     */
    private $orderFrom;

    /**
     * @var int $payType 1 支付宝 2余额 3:微信 4:平安支付 5:信用付
     * @Column(name="pay_type", type="tinyint", default=0)
     */
    private $payType;

    /**
     * @var int $buyerId 买家ID
     * @Column(name="buyer_id", type="integer", default=0)
     */
    private $buyerId;

    /**
     * @var int $oldBuyerId 合并数据原归属ID
     * @Column(name="old_buyer_id", type="integer", default=0)
     */
    private $oldBuyerId;

    /**
     * @var string $buyerName 买家名称
     * @Column(name="buyer_name", type="string", length=30, default="''")
     */
    private $buyerName;

    /**
     * @var int $sellerId 卖家ID
     * @Column(name="seller_id", type="integer", default=0)
     */
    private $sellerId;

    /**
     * @var int $oldSellerId 合并数据原归属ID
     * @Column(name="old_seller_id", type="integer", default=0)
     */
    private $oldSellerId;

    /**
     * @var string $sellerName 卖家名称
     * @Column(name="seller_name", type="string", length=30, default="''")
     */
    private $sellerName;

    /**
     * @var string $consignee 收货人
     * @Column(name="consignee", type="string", length=50, default="''")
     */
    private $consignee;

    /**
     * @var int $provinceId 省份ID
     * @Column(name="province_id", type="integer", default=0)
     */
    private $provinceId;

    /**
     * @var string $province 省名
     * @Column(name="province", type="string", length=20, default="''")
     */
    private $province;

    /**
     * @var int $cityId 市ID
     * @Column(name="city_id", type="integer", default=0)
     */
    private $cityId;

    /**
     * @var string $city 市名
     * @Column(name="city", type="string", length=20, default="''")
     */
    private $city;

    /**
     * @var int $areaId 区ID
     * @Column(name="area_id", type="integer", default=0)
     */
    private $areaId;

    /**
     * @var string $area 区名
     * @Column(name="area", type="string", length=20, default="''")
     */
    private $area;

    /**
     * @var string $detailAddress 收货地址
     * @Column(name="detail_address", type="string", length=255, default="''")
     */
    private $detailAddress;

    /**
     * @var string $contactPhone 联系电话
     * @Column(name="contact_phone", type="string", length=20, default="''")
     */
    private $contactPhone;

    /**
     * @var string $expressInfo 快递信息
     * @Column(name="express_info", type="string", length=300, default="''")
     */
    private $expressInfo;

    /**
     * @var string $buyerMessage 买家留言
     * @Column(name="buyer_message", type="string", length=255, default="''")
     */
    private $buyerMessage;

    /**
     * @var int $status 订单基本状态:1.待付款2.待发货3.已发货4.交易成功5.交易关闭
     * @Column(name="status", type="tinyint", default=0)
     */
    private $status;

    /**
     * @var int $secStatus 1.买家申请退款2.卖家拒绝退款3.退款关闭4.卖家同意退款5.退款取消 6.买家发起申诉7.官方已介入申诉8.申诉结束（买家确认收货、申诉完成）9.买家取消申诉 10.申诉关闭（商家发货) 11申诉关闭(买家取消申诉)
     * @Column(name="sec_status", type="tinyint", default=0)
     */
    private $secStatus;

    /**
     * @var int $returnGoodsStatus 退货状态 10:提交申请成功 20:卖家同意 30:买家发货 40:确认收货 90:卖家不同意退货 91:买家未发货 92:卖家未确认收货(未收到货等情况) 93:卖家拒绝退款,买家申诉 99:取消退货
     * @Column(name="return_goods_status", type="smallint", default=0)
     */
    private $returnGoodsStatus;

    /**
     * @var int $delStatus 订单状态 1正常 2删除
     * @Column(name="del_status", type="tinyint", default=1)
     */
    private $delStatus;

    /**
     * @var int $expressTime 物流信息时间
     * @Column(name="express_time", type="integer", default=0)
     */
    private $expressTime;

    /**
     * @var int $addTime 创建时间
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

    /**
     * @var int $payTime 付款时间
     * @Column(name="pay_time", type="integer", default=0)
     */
    private $payTime;

    /**
     * @var int $cancelTime 订单取消时间
     * @Column(name="cancel_time", type="integer", default=0)
     */
    private $cancelTime;

    /**
     * @var int $sendTime 发货时间
     * @Column(name="send_time", type="integer", default=0)
     */
    private $sendTime;

    /**
     * @var int $autoTakeTime 自动确认收货时间
     * @Column(name="auto_take_time", type="integer", default=0)
     */
    private $autoTakeTime;

    /**
     * @var int $takeTime 确认收货时间
     * @Column(name="take_time", type="integer", default=0)
     */
    private $takeTime;

    /**
     * @var float $realGet 卖家实际获取金额
     * @Column(name="real_get", type="double", default=0)
     */
    private $realGet;

    /**
     * @var int $hasScore 是否已评论0.否 1.是
     * @Column(name="has_score", type="tinyint", default=0)
     */
    private $hasScore;

    /**
     * @var int $shipType 运费类型 1:运费到付 2:包邮 3：运费x元 4:自提
     * @Column(name="ship_type", type="tinyint", default=0)
     */
    private $shipType;

    /**
     * @var int $hasElecCode 是否有电子码单 1:是 0：否
     * @Column(name="has_elec_code", type="tinyint", default=0)
     */
    private $hasElecCode;

    /**
     * @var int $isFastArrival 是否极速到账 0:否 1:是
     * @Column(name="is_fast_arrival", type="tinyint", default=0)
     */
    private $isFastArrival;

    /**
     * @var int $updateTime 修改时间
     * @Column(name="update_time", type="integer", default=0)
     */
    private $updateTime;

    /**
     * @var float $changePrice 改价的差价
     * @Column(name="change_price", type="double", default=0)
     */
    private $changePrice;

    /**
     * @param int $value
     * @return $this
     */
    public function setOrderId(int $value)
    {
        $this->orderId = $value;

        return $this;
    }

    /**
     * 订单编号
     * @param string $value
     * @return $this
     */
    public function setOrderNum(string $value): self
    {
        $this->orderNum = $value;

        return $this;
    }

    /**
     * 总价
     * @param float $value
     * @return $this
     */
    public function setTotalOrderPrice(float $value): self
    {
        $this->totalOrderPrice = $value;

        return $this;
    }

    /**
     * 运费
     * @param float $value
     * @return $this
     */
    public function setYunfei(float $value): self
    {
        $this->yunfei = $value;

        return $this;
    }

    /**
     * 优惠id
     * @param int $value
     * @return $this
     */
    public function setRuleId(int $value): self
    {
        $this->ruleId = $value;

        return $this;
    }

    /**
     * 购买类型1:下单 2:精品 3：产品 4:开单 5:购物车
     * @param int $value
     * @return $this
     */
    public function setRuleType(int $value): self
    {
        $this->ruleType = $value;

        return $this;
    }

    /**
     * 下单来源: 1:app首页一键下单 2:app首页一键开单 3:聊天下单 4:聊天开单 5:立即购买 6:购物车下单 7:收到报价下单 8:微信聊天下单 9:微信聊天开单 10:微信商品购买 11:微信购物车下单 12:微信报价下单 13:再来一单
     * @param int $value
     * @return $this
     */
    public function setOrderSource(int $value): self
    {
        $this->orderSource = $value;

        return $this;
    }

    /**
     * 优惠的金额
     * @param float $value
     * @return $this
     */
    public function setRulePrice(float $value): self
    {
        $this->rulePrice = $value;

        return $this;
    }

    /**
     * 优惠券id
     * @param int $value
     * @return $this
     */
    public function setCouponId(int $value): self
    {
        $this->couponId = $value;

        return $this;
    }

    /**
     * 优惠券优惠金额
     * @param float $value
     * @return $this
     */
    public function setCouponPrice(float $value): self
    {
        $this->couponPrice = $value;

        return $this;
    }

    /**
     * 订单来源 0、APP 1、安卓 2、IOS 3、微信 99:批量下单 98:web色卡批量购买 9、微信(接口合并后新增)
     * @param int $value
     * @return $this
     */
    public function setFromType(int $value): self
    {
        $this->fromType = $value;

        return $this;
    }

    /**
     * 订单发起者：0，买家 1，卖家
     * @param int $value
     * @return $this
     */
    public function setOrderFrom(int $value): self
    {
        $this->orderFrom = $value;

        return $this;
    }

    /**
     * 1 支付宝 2余额 3:微信 4:平安支付 5:信用付
     * @param int $value
     * @return $this
     */
    public function setPayType(int $value): self
    {
        $this->payType = $value;

        return $this;
    }

    /**
     * 买家ID
     * @param int $value
     * @return $this
     */
    public function setBuyerId(int $value): self
    {
        $this->buyerId = $value;

        return $this;
    }

    /**
     * 合并数据原归属ID
     * @param int $value
     * @return $this
     */
    public function setOldBuyerId(int $value): self
    {
        $this->oldBuyerId = $value;

        return $this;
    }

    /**
     * 买家名称
     * @param string $value
     * @return $this
     */
    public function setBuyerName(string $value): self
    {
        $this->buyerName = $value;

        return $this;
    }

    /**
     * 卖家ID
     * @param int $value
     * @return $this
     */
    public function setSellerId(int $value): self
    {
        $this->sellerId = $value;

        return $this;
    }

    /**
     * 合并数据原归属ID
     * @param int $value
     * @return $this
     */
    public function setOldSellerId(int $value): self
    {
        $this->oldSellerId = $value;

        return $this;
    }

    /**
     * 卖家名称
     * @param string $value
     * @return $this
     */
    public function setSellerName(string $value): self
    {
        $this->sellerName = $value;

        return $this;
    }

    /**
     * 收货人
     * @param string $value
     * @return $this
     */
    public function setConsignee(string $value): self
    {
        $this->consignee = $value;

        return $this;
    }

    /**
     * 省份ID
     * @param int $value
     * @return $this
     */
    public function setProvinceId(int $value): self
    {
        $this->provinceId = $value;

        return $this;
    }

    /**
     * 省名
     * @param string $value
     * @return $this
     */
    public function setProvince(string $value): self
    {
        $this->province = $value;

        return $this;
    }

    /**
     * 市ID
     * @param int $value
     * @return $this
     */
    public function setCityId(int $value): self
    {
        $this->cityId = $value;

        return $this;
    }

    /**
     * 市名
     * @param string $value
     * @return $this
     */
    public function setCity(string $value): self
    {
        $this->city = $value;

        return $this;
    }

    /**
     * 区ID
     * @param int $value
     * @return $this
     */
    public function setAreaId(int $value): self
    {
        $this->areaId = $value;

        return $this;
    }

    /**
     * 区名
     * @param string $value
     * @return $this
     */
    public function setArea(string $value): self
    {
        $this->area = $value;

        return $this;
    }

    /**
     * 收货地址
     * @param string $value
     * @return $this
     */
    public function setDetailAddress(string $value): self
    {
        $this->detailAddress = $value;

        return $this;
    }

    /**
     * 联系电话
     * @param string $value
     * @return $this
     */
    public function setContactPhone(string $value): self
    {
        $this->contactPhone = $value;

        return $this;
    }

    /**
     * 快递信息
     * @param string $value
     * @return $this
     */
    public function setExpressInfo(string $value): self
    {
        $this->expressInfo = $value;

        return $this;
    }

    /**
     * 买家留言
     * @param string $value
     * @return $this
     */
    public function setBuyerMessage(string $value): self
    {
        $this->buyerMessage = $value;

        return $this;
    }

    /**
     * 订单基本状态:1.待付款2.待发货3.已发货4.交易成功5.交易关闭
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * 1.买家申请退款2.卖家拒绝退款3.退款关闭4.卖家同意退款5.退款取消 6.买家发起申诉7.官方已介入申诉8.申诉结束（买家确认收货、申诉完成）9.买家取消申诉 10.申诉关闭（商家发货) 11申诉关闭(买家取消申诉)
     * @param int $value
     * @return $this
     */
    public function setSecStatus(int $value): self
    {
        $this->secStatus = $value;

        return $this;
    }

    /**
     * 退货状态 10:提交申请成功 20:卖家同意 30:买家发货 40:确认收货 90:卖家不同意退货 91:买家未发货 92:卖家未确认收货(未收到货等情况) 93:卖家拒绝退款,买家申诉 99:取消退货
     * @param int $value
     * @return $this
     */
    public function setReturnGoodsStatus(int $value): self
    {
        $this->returnGoodsStatus = $value;

        return $this;
    }

    /**
     * 订单状态 1正常 2删除
     * @param int $value
     * @return $this
     */
    public function setDelStatus(int $value): self
    {
        $this->delStatus = $value;

        return $this;
    }

    /**
     * 物流信息时间
     * @param int $value
     * @return $this
     */
    public function setExpressTime(int $value): self
    {
        $this->expressTime = $value;

        return $this;
    }

    /**
     * 创建时间
     * @param int $value
     * @return $this
     */
    public function setAddTime(int $value): self
    {
        $this->addTime = $value;

        return $this;
    }

    /**
     * 付款时间
     * @param int $value
     * @return $this
     */
    public function setPayTime(int $value): self
    {
        $this->payTime = $value;

        return $this;
    }

    /**
     * 订单取消时间
     * @param int $value
     * @return $this
     */
    public function setCancelTime(int $value): self
    {
        $this->cancelTime = $value;

        return $this;
    }

    /**
     * 发货时间
     * @param int $value
     * @return $this
     */
    public function setSendTime(int $value): self
    {
        $this->sendTime = $value;

        return $this;
    }

    /**
     * 自动确认收货时间
     * @param int $value
     * @return $this
     */
    public function setAutoTakeTime(int $value): self
    {
        $this->autoTakeTime = $value;

        return $this;
    }

    /**
     * 确认收货时间
     * @param int $value
     * @return $this
     */
    public function setTakeTime(int $value): self
    {
        $this->takeTime = $value;

        return $this;
    }

    /**
     * 卖家实际获取金额
     * @param float $value
     * @return $this
     */
    public function setRealGet(float $value): self
    {
        $this->realGet = $value;

        return $this;
    }

    /**
     * 是否已评论0.否 1.是
     * @param int $value
     * @return $this
     */
    public function setHasScore(int $value): self
    {
        $this->hasScore = $value;

        return $this;
    }

    /**
     * 运费类型 1:运费到付 2:包邮 3：运费x元 4:自提
     * @param int $value
     * @return $this
     */
    public function setShipType(int $value): self
    {
        $this->shipType = $value;

        return $this;
    }

    /**
     * 是否有电子码单 1:是 0：否
     * @param int $value
     * @return $this
     */
    public function setHasElecCode(int $value): self
    {
        $this->hasElecCode = $value;

        return $this;
    }

    /**
     * 是否极速到账 0:否 1:是
     * @param int $value
     * @return $this
     */
    public function setIsFastArrival(int $value): self
    {
        $this->isFastArrival = $value;

        return $this;
    }

    /**
     * 修改时间
     * @param int $value
     * @return $this
     */
    public function setUpdateTime(int $value): self
    {
        $this->updateTime = $value;

        return $this;
    }

    /**
     * 改价的差价
     * @param float $value
     * @return $this
     */
    public function setChangePrice(float $value): self
    {
        $this->changePrice = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * 订单编号
     * @return mixed
     */
    public function getOrderNum()
    {
        return $this->orderNum;
    }

    /**
     * 总价
     * @return float
     */
    public function getTotalOrderPrice()
    {
        return $this->totalOrderPrice;
    }

    /**
     * 运费
     * @return float
     */
    public function getYunfei()
    {
        return $this->yunfei;
    }

    /**
     * 优惠id
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * 购买类型1:下单 2:精品 3：产品 4:开单 5:购物车
     * @return int
     */
    public function getRuleType()
    {
        return $this->ruleType;
    }

    /**
     * 下单来源: 1:app首页一键下单 2:app首页一键开单 3:聊天下单 4:聊天开单 5:立即购买 6:购物车下单 7:收到报价下单 8:微信聊天下单 9:微信聊天开单 10:微信商品购买 11:微信购物车下单 12:微信报价下单 13:再来一单
     * @return int
     */
    public function getOrderSource()
    {
        return $this->orderSource;
    }

    /**
     * 优惠的金额
     * @return float
     */
    public function getRulePrice()
    {
        return $this->rulePrice;
    }

    /**
     * 优惠券id
     * @return int
     */
    public function getCouponId()
    {
        return $this->couponId;
    }

    /**
     * 优惠券优惠金额
     * @return float
     */
    public function getCouponPrice()
    {
        return $this->couponPrice;
    }

    /**
     * 订单来源 0、APP 1、安卓 2、IOS 3、微信 99:批量下单 98:web色卡批量购买 9、微信(接口合并后新增)
     * @return int
     */
    public function getFromType()
    {
        return $this->fromType;
    }

    /**
     * 订单发起者：0，买家 1，卖家
     * @return int
     */
    public function getOrderFrom()
    {
        return $this->orderFrom;
    }

    /**
     * 1 支付宝 2余额 3:微信 4:平安支付 5:信用付
     * @return int
     */
    public function getPayType()
    {
        return $this->payType;
    }

    /**
     * 买家ID
     * @return int
     */
    public function getBuyerId()
    {
        return $this->buyerId;
    }

    /**
     * 合并数据原归属ID
     * @return int
     */
    public function getOldBuyerId()
    {
        return $this->oldBuyerId;
    }

    /**
     * 买家名称
     * @return mixed
     */
    public function getBuyerName()
    {
        return $this->buyerName;
    }

    /**
     * 卖家ID
     * @return int
     */
    public function getSellerId()
    {
        return $this->sellerId;
    }

    /**
     * 合并数据原归属ID
     * @return int
     */
    public function getOldSellerId()
    {
        return $this->oldSellerId;
    }

    /**
     * 卖家名称
     * @return mixed
     */
    public function getSellerName()
    {
        return $this->sellerName;
    }

    /**
     * 收货人
     * @return mixed
     */
    public function getConsignee()
    {
        return $this->consignee;
    }

    /**
     * 省份ID
     * @return int
     */
    public function getProvinceId()
    {
        return $this->provinceId;
    }

    /**
     * 省名
     * @return mixed
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * 市ID
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * 市名
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * 区ID
     * @return int
     */
    public function getAreaId()
    {
        return $this->areaId;
    }

    /**
     * 区名
     * @return mixed
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * 收货地址
     * @return mixed
     */
    public function getDetailAddress()
    {
        return $this->detailAddress;
    }

    /**
     * 联系电话
     * @return mixed
     */
    public function getContactPhone()
    {
        return $this->contactPhone;
    }

    /**
     * 快递信息
     * @return mixed
     */
    public function getExpressInfo()
    {
        return $this->expressInfo;
    }

    /**
     * 买家留言
     * @return mixed
     */
    public function getBuyerMessage()
    {
        return $this->buyerMessage;
    }

    /**
     * 订单基本状态:1.待付款2.待发货3.已发货4.交易成功5.交易关闭
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 1.买家申请退款2.卖家拒绝退款3.退款关闭4.卖家同意退款5.退款取消 6.买家发起申诉7.官方已介入申诉8.申诉结束（买家确认收货、申诉完成）9.买家取消申诉 10.申诉关闭（商家发货) 11申诉关闭(买家取消申诉)
     * @return int
     */
    public function getSecStatus()
    {
        return $this->secStatus;
    }

    /**
     * 退货状态 10:提交申请成功 20:卖家同意 30:买家发货 40:确认收货 90:卖家不同意退货 91:买家未发货 92:卖家未确认收货(未收到货等情况) 93:卖家拒绝退款,买家申诉 99:取消退货
     * @return int
     */
    public function getReturnGoodsStatus()
    {
        return $this->returnGoodsStatus;
    }

    /**
     * 订单状态 1正常 2删除
     * @return mixed
     */
    public function getDelStatus()
    {
        return $this->delStatus;
    }

    /**
     * 物流信息时间
     * @return int
     */
    public function getExpressTime()
    {
        return $this->expressTime;
    }

    /**
     * 创建时间
     * @return int
     */
    public function getAddTime()
    {
        return $this->addTime;
    }

    /**
     * 付款时间
     * @return int
     */
    public function getPayTime()
    {
        return $this->payTime;
    }

    /**
     * 订单取消时间
     * @return int
     */
    public function getCancelTime()
    {
        return $this->cancelTime;
    }

    /**
     * 发货时间
     * @return int
     */
    public function getSendTime()
    {
        return $this->sendTime;
    }

    /**
     * 自动确认收货时间
     * @return int
     */
    public function getAutoTakeTime()
    {
        return $this->autoTakeTime;
    }

    /**
     * 确认收货时间
     * @return int
     */
    public function getTakeTime()
    {
        return $this->takeTime;
    }

    /**
     * 卖家实际获取金额
     * @return float
     */
    public function getRealGet()
    {
        return $this->realGet;
    }

    /**
     * 是否已评论0.否 1.是
     * @return int
     */
    public function getHasScore()
    {
        return $this->hasScore;
    }

    /**
     * 运费类型 1:运费到付 2:包邮 3：运费x元 4:自提
     * @return int
     */
    public function getShipType()
    {
        return $this->shipType;
    }

    /**
     * 是否有电子码单 1:是 0：否
     * @return int
     */
    public function getHasElecCode()
    {
        return $this->hasElecCode;
    }

    /**
     * 是否极速到账 0:否 1:是
     * @return int
     */
    public function getIsFastArrival()
    {
        return $this->isFastArrival;
    }

    /**
     * 修改时间
     * @return int
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    /**
     * 改价的差价
     * @return float
     */
    public function getChangePrice()
    {
        return $this->changePrice;
    }

}
