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
 * 购物车日志表
 * @Entity()
 * @Table(name="sb_order_cart_buried_log")
 * @uses      OrderCartBuried
 */
class OrderCartBuried extends Model
{
    /**
     * @var int $ocbId 
     * @Id()
     * @Column(name="ocb_id", type="integer")
     */
    private $ocbId;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

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
     * @Column(name="amount", type="integer", default=0)
     */
    private $amount;

    /**
     * @var int $status 购物车产品状态 1:添加到购物车 2:已购买 3:已删除 4:已失效	
     * @Column(name="status", type="tinyint", default=0)
     */
    private $status;

    /**
     * @var int $operationTime 操作时间
     * @Column(name="operation_time", type="integer", default=0)
     */
    private $operationTime;

    /**
     * @param int $value
     * @return $this
     */
    public function setOcbId(int $value)
    {
        $this->ocbId = $value;

        return $this;
    }

    /**
     * 用户id
     * @param int $value
     * @return $this
     */
    public function setUserId(int $value): self
    {
        $this->userId = $value;

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
     * 购物车产品状态 1:添加到购物车 2:已购买 3:已删除 4:已失效	
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * 操作时间
     * @param int $value
     * @return $this
     */
    public function setOperationTime(int $value): self
    {
        $this->operationTime = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOcbId()
    {
        return $this->ocbId;
    }

    /**
     * 用户id
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
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
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * 购物车产品状态 1:添加到购物车 2:已购买 3:已删除 4:已失效	
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 操作时间
     * @return int
     */
    public function getOperationTime()
    {
        return $this->operationTime;
    }

}
