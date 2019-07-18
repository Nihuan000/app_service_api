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
 * 购物车表

 * @Entity()
 * @Table(name="sb_order_cart")
 * @uses      OrderCart
 */
class OrderCart extends Model
{
    /**
     * @var int $cartId 
     * @Id()
     * @Column(name="cart_id", type="integer")
     */
    private $cartId;

    /**
     * @var int $userId 当前用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $shopId 店铺id
     * @Column(name="shop_id", type="integer", default=0)
     */
    private $shopId;

    /**
     * @var int $proId 产品id
     * @Column(name="pro_id", type="integer", default=0)
     */
    private $proId;

    /**
     * @var int $orderType 购买类型 1:色卡 2:剪样 3:大货
     * @Column(name="order_type", type="tinyint", default=0)
     */
    private $orderType;

    /**
     * @var int $amount 购买数量
     * @Column(name="amount", type="integer", default=1)
     */
    private $amount;

    /**
     * @var int $ruleId 优惠id
     * @Column(name="rule_id", type="integer", default=0)
     */
    private $ruleId;

    /**
     * @var int $status 产品状态 1:正常 2:已购买 3:已删除 4:已失效
     * @Column(name="status", type="tinyint", default=1)
     */
    private $status;

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
     * @var int $freshTime 刷新时间
     * @Column(name="fresh_time", type="integer", default=0)
     */
    private $freshTime;

    /**
     * @param int $value
     * @return $this
     */
    public function setCartId(int $value)
    {
        $this->cartId = $value;

        return $this;
    }

    /**
     * 当前用户id
     * @param int $value
     * @return $this
     */
    public function setUserId(int $value): self
    {
        $this->userId = $value;

        return $this;
    }

    /**
     * 店铺id
     * @param int $value
     * @return $this
     */
    public function setShopId(int $value): self
    {
        $this->shopId = $value;

        return $this;
    }

    /**
     * 产品id
     * @param int $value
     * @return $this
     */
    public function setProId(int $value): self
    {
        $this->proId = $value;

        return $this;
    }

    /**
     * 购买类型 1:色卡 2:剪样 3:大货
     * @param int $value
     * @return $this
     */
    public function setOrderType(int $value): self
    {
        $this->orderType = $value;

        return $this;
    }

    /**
     * 购买数量
     * @param int $value
     * @return $this
     */
    public function setAmount(int $value): self
    {
        $this->amount = $value;

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
     * 产品状态 1:正常 2:已购买 3:已删除 4:已失效
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

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
     * 刷新时间
     * @param int $value
     * @return $this
     */
    public function setFreshTime(int $value): self
    {
        $this->freshTime = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * 当前用户id
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 店铺id
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * 产品id
     * @return int
     */
    public function getProId()
    {
        return $this->proId;
    }

    /**
     * 购买类型 1:色卡 2:剪样 3:大货
     * @return int
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * 购买数量
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
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
     * 产品状态 1:正常 2:已购买 3:已删除 4:已失效
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
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
     * 刷新时间
     * @return int
     */
    public function getFreshTime()
    {
        return $this->freshTime;
    }

}
