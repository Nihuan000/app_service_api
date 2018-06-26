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
 * 订单详情表
 * @Entity()
 * @Table(name="sb_order_product")
 * @uses      OrderProduct
 */
class OrderProduct extends Model
{
    /**
     * @var int $opId 
     * @Id()
     * @Column(name="op_id", type="integer")
     */
    private $opId;

    /**
     * @var string $orderNum 订单号
     * @Column(name="order_num", type="string", length=32, default="''")
     */
    private $orderNum;

    /**
     * @var int $sellerId 卖家id
     * @Column(name="seller_id", type="integer", default=0)
     */
    private $sellerId;

    /**
     * @var string $sellerName 卖家名
     * @Column(name="seller_name", type="string", length=150, default="''")
     */
    private $sellerName;

    /**
     * @var int $proId 产品/精品ID 其他类型为0
     * @Column(name="pro_id", type="integer", default=0)
     */
    private $proId;

    /**
     * @var int $ruleId 无效字段
     * @Column(name="rule_id", type="integer", default=0)
     */
    private $ruleId;

    /**
     * @var float $rulePrice 无效字段
     * @Column(name="rule_price", type="float", default=0)
     */
    private $rulePrice;

    /**
     * @var int $offerType 无效字段
     * @Column(name="offer_type", type="tinyint", default=0)
     */
    private $offerType;

    /**
     * @var int $proType 订单产品类型1:精品 2:产品 3:开单取样 4:开单大货
     * @Column(name="pro_type", type="tinyint", default=0)
     */
    private $proType;

    /**
     * @var string $title 标题
     * @Column(name="title", type="string", length=255, default="''")
     */
    private $title;

    /**
     * @var string $info 详情
     * @Column(name="info", type="string", length=255, default="''")
     */
    private $info;

    /**
     * @var string $pic 图片
     * @Column(name="pic", type="string", length=100, default="''")
     */
    private $pic;

    /**
     * @var int $type 订单类型 1剪样 2大货 3:色卡 4:剪样
     * @Column(name="type", type="tinyint", default=0)
     */
    private $type;

    /**
     * @var float $price 产品单价 没有产品值为0
     * @Column(name="price", type="decimal", default=0)
     */
    private $price;

    /**
     * @var int $amount 个数\数量
     * @Column(name="amount", type="integer", default=1)
     */
    private $amount;

    /**
     * @var string $unit 单位
     * @Column(name="unit", type="string", length=30, default="''")
     */
    private $unit;

    /**
     * @param int $value
     * @return $this
     */
    public function setOpId(int $value)
    {
        $this->opId = $value;

        return $this;
    }

    /**
     * 订单号
     * @param string $value
     * @return $this
     */
    public function setOrderNum(string $value): self
    {
        $this->orderNum = $value;

        return $this;
    }

    /**
     * 卖家id
     * @param int $value
     * @return $this
     */
    public function setSellerId(int $value): self
    {
        $this->sellerId = $value;

        return $this;
    }

    /**
     * 卖家名
     * @param string $value
     * @return $this
     */
    public function setSellerName(string $value): self
    {
        $this->sellerName = $value;

        return $this;
    }

    /**
     * 产品/精品ID 其他类型为0
     * @param int $value
     * @return $this
     */
    public function setProId(int $value): self
    {
        $this->proId = $value;

        return $this;
    }

    /**
     * 无效字段
     * @param int $value
     * @return $this
     */
    public function setRuleId(int $value): self
    {
        $this->ruleId = $value;

        return $this;
    }

    /**
     * 无效字段
     * @param float $value
     * @return $this
     */
    public function setRulePrice(float $value): self
    {
        $this->rulePrice = $value;

        return $this;
    }

    /**
     * 无效字段
     * @param int $value
     * @return $this
     */
    public function setOfferType(int $value): self
    {
        $this->offerType = $value;

        return $this;
    }

    /**
     * 订单产品类型1:精品 2:产品 3:开单取样 4:开单大货
     * @param int $value
     * @return $this
     */
    public function setProType(int $value): self
    {
        $this->proType = $value;

        return $this;
    }

    /**
     * 标题
     * @param string $value
     * @return $this
     */
    public function setTitle(string $value): self
    {
        $this->title = $value;

        return $this;
    }

    /**
     * 详情
     * @param string $value
     * @return $this
     */
    public function setInfo(string $value): self
    {
        $this->info = $value;

        return $this;
    }

    /**
     * 图片
     * @param string $value
     * @return $this
     */
    public function setPic(string $value): self
    {
        $this->pic = $value;

        return $this;
    }

    /**
     * 订单类型 1剪样 2大货 3:色卡 4:剪样
     * @param int $value
     * @return $this
     */
    public function setType(int $value): self
    {
        $this->type = $value;

        return $this;
    }

    /**
     * 产品单价 没有产品值为0
     * @param float $value
     * @return $this
     */
    public function setPrice(float $value): self
    {
        $this->price = $value;

        return $this;
    }

    /**
     * 个数\数量
     * @param int $value
     * @return $this
     */
    public function setAmount(int $value): self
    {
        $this->amount = $value;

        return $this;
    }

    /**
     * 单位
     * @param string $value
     * @return $this
     */
    public function setUnit(string $value): self
    {
        $this->unit = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOpId()
    {
        return $this->opId;
    }

    /**
     * 订单号
     * @return mixed
     */
    public function getOrderNum()
    {
        return $this->orderNum;
    }

    /**
     * 卖家id
     * @return int
     */
    public function getSellerId()
    {
        return $this->sellerId;
    }

    /**
     * 卖家名
     * @return mixed
     */
    public function getSellerName()
    {
        return $this->sellerName;
    }

    /**
     * 产品/精品ID 其他类型为0
     * @return int
     */
    public function getProId()
    {
        return $this->proId;
    }

    /**
     * 无效字段
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * 无效字段
     * @return float
     */
    public function getRulePrice()
    {
        return $this->rulePrice;
    }

    /**
     * 无效字段
     * @return int
     */
    public function getOfferType()
    {
        return $this->offerType;
    }

    /**
     * 订单产品类型1:精品 2:产品 3:开单取样 4:开单大货
     * @return int
     */
    public function getProType()
    {
        return $this->proType;
    }

    /**
     * 标题
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * 详情
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * 图片
     * @return mixed
     */
    public function getPic()
    {
        return $this->pic;
    }

    /**
     * 订单类型 1剪样 2大货 3:色卡 4:剪样
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 产品单价 没有产品值为0
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * 个数\数量
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * 单位
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

}
