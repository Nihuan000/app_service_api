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
 * 订单产品关联表
 * @Entity()
 * @Table(name="sb_order_product_relation")
 * @uses      OrderProductRelation
 */
class OrderProductRelation extends Model
{
    /**
     * @var int $subId 
     * @Id()
     * @Column(name="sub_id", type="integer")
     */
    private $subId;

    /**
     * @var string $orderNum 订单编号
     * @Column(name="order_num", type="string", length=32, default="''")
     */
    private $orderNum;

    /**
     * @var string $orderSubNum 子编号
     * @Column(name="order_sub_num", type="string", length=32, default="''")
     */
    private $orderSubNum;

    /**
     * @var int $elecOrder 电子码单信息？ 1：是 0：否
     * @Column(name="elec_order", type="tinyint", default=0)
     */
    private $elecOrder;

    /**
     * @var float $totalPrice 订单产品总金额
     * @Column(name="total_price", type="float", default=0)
     */
    private $totalPrice;

    /**
     * @var int $proRuleId 优惠Id
     * @Column(name="pro_rule_id", type="integer", default=0)
     */
    private $proRuleId;

    /**
     * @var int $proRuleType 优惠类型 1：商品 2：全场 3:店铺满减 4:产品满减 5:跨产品满减 6:立减规则(运营后台) 7:限时折扣(运营后台)
     * @Column(name="pro_rule_type", type="tinyint", default=0)
     */
    private $proRuleType;

    /**
     * @var float $proRulePrice 商品优惠金额
     * @Column(name="pro_rule_price", type="float", default=0)
     */
    private $proRulePrice;

    /**
     * @var int $status 子订单状态 1.买家申请退款2.卖家拒绝退款3.退款关闭4.卖家同意退款5.退款取消 6.买家发起申诉7.官方已介入申诉8.申诉结束（买家确认收货、申诉完成）9.买家取消申诉 10.申诉关闭（商家发货) 11申诉关闭(买家取消申诉)
     * @Column(name="status", type="tinyint", default=0)
     */
    private $status;

    /**
     * @var int $returnGoodsStatus 退货状态 10:提交申请成功 20:卖家同意 30:买家发货 40:确认收货 90:卖家不同意退货 91:买家未发货 92:卖家未确认收货(未收到货等情况) 93:卖家拒绝退款,买家申诉 99:取消退货
     * @Column(name="return_goods_status", type="smallint")
     * @Required()
     */
    private $returnGoodsStatus;

    /**
     * @var int $addTime 添加时间
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

    /**
     * @var int $updateTime 修改时间
     * @Column(name="update_time", type="integer", default=0)
     */
    private $updateTime;

    /**
     * @var int $proCouponId 产品优惠券id
     * @Column(name="pro_coupon_id", type="integer", default=0)
     */
    private $proCouponId;

    /**
     * @var float $proCouponPrice 优惠券优惠金额
     * @Column(name="pro_coupon_price", type="float", default=0)
     */
    private $proCouponPrice;

    /**
     * @var float $proRealGet 子订单卖家待收金额
     * @Column(name="pro_real_get", type="decimal", default=0)
     */
    private $proRealGet;

    /**
     * @param int $value
     * @return $this
     */
    public function setSubId(int $value)
    {
        $this->subId = $value;

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
     * 子编号
     * @param string $value
     * @return $this
     */
    public function setOrderSubNum(string $value): self
    {
        $this->orderSubNum = $value;

        return $this;
    }

    /**
     * 电子码单信息？ 1：是 0：否
     * @param int $value
     * @return $this
     */
    public function setElecOrder(int $value): self
    {
        $this->elecOrder = $value;

        return $this;
    }

    /**
     * 订单产品总金额
     * @param float $value
     * @return $this
     */
    public function setTotalPrice(float $value): self
    {
        $this->totalPrice = $value;

        return $this;
    }

    /**
     * 优惠Id
     * @param int $value
     * @return $this
     */
    public function setProRuleId(int $value): self
    {
        $this->proRuleId = $value;

        return $this;
    }

    /**
     * 优惠类型 1：商品 2：全场 3:店铺满减 4:产品满减 5:跨产品满减 6:立减规则(运营后台) 7:限时折扣(运营后台)
     * @param int $value
     * @return $this
     */
    public function setProRuleType(int $value): self
    {
        $this->proRuleType = $value;

        return $this;
    }

    /**
     * 商品优惠金额
     * @param float $value
     * @return $this
     */
    public function setProRulePrice(float $value): self
    {
        $this->proRulePrice = $value;

        return $this;
    }

    /**
     * 子订单状态 1.买家申请退款2.卖家拒绝退款3.退款关闭4.卖家同意退款5.退款取消 6.买家发起申诉7.官方已介入申诉8.申诉结束（买家确认收货、申诉完成）9.买家取消申诉 10.申诉关闭（商家发货) 11申诉关闭(买家取消申诉)
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

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
     * 添加时间
     * @param int $value
     * @return $this
     */
    public function setAddTime(int $value): self
    {
        $this->addTime = $value;

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
     * 产品优惠券id
     * @param int $value
     * @return $this
     */
    public function setProCouponId(int $value): self
    {
        $this->proCouponId = $value;

        return $this;
    }

    /**
     * 优惠券优惠金额
     * @param float $value
     * @return $this
     */
    public function setProCouponPrice(float $value): self
    {
        $this->proCouponPrice = $value;

        return $this;
    }

    /**
     * 子订单卖家待收金额
     * @param float $value
     * @return $this
     */
    public function setProRealGet(float $value): self
    {
        $this->proRealGet = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubId()
    {
        return $this->subId;
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
     * 子编号
     * @return mixed
     */
    public function getOrderSubNum()
    {
        return $this->orderSubNum;
    }

    /**
     * 电子码单信息？ 1：是 0：否
     * @return int
     */
    public function getElecOrder()
    {
        return $this->elecOrder;
    }

    /**
     * 订单产品总金额
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * 优惠Id
     * @return int
     */
    public function getProRuleId()
    {
        return $this->proRuleId;
    }

    /**
     * 优惠类型 1：商品 2：全场 3:店铺满减 4:产品满减 5:跨产品满减 6:立减规则(运营后台) 7:限时折扣(运营后台)
     * @return int
     */
    public function getProRuleType()
    {
        return $this->proRuleType;
    }

    /**
     * 商品优惠金额
     * @return float
     */
    public function getProRulePrice()
    {
        return $this->proRulePrice;
    }

    /**
     * 子订单状态 1.买家申请退款2.卖家拒绝退款3.退款关闭4.卖家同意退款5.退款取消 6.买家发起申诉7.官方已介入申诉8.申诉结束（买家确认收货、申诉完成）9.买家取消申诉 10.申诉关闭（商家发货) 11申诉关闭(买家取消申诉)
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
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
     * 添加时间
     * @return int
     */
    public function getAddTime()
    {
        return $this->addTime;
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
     * 产品优惠券id
     * @return int
     */
    public function getProCouponId()
    {
        return $this->proCouponId;
    }

    /**
     * 优惠券优惠金额
     * @return float
     */
    public function getProCouponPrice()
    {
        return $this->proCouponPrice;
    }

    /**
     * 子订单卖家待收金额
     * @return mixed
     */
    public function getProRealGet()
    {
        return $this->proRealGet;
    }

}
