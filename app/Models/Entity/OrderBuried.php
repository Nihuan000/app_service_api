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
 * 订单变更日志表
 * @Entity()
 * @Table(name="sb_order_buried_log")
 * @uses      OrderBuried
 */
class OrderBuried extends Model
{
    /**
     * @var int $obId 
     * @Id()
     * @Column(name="ob_id", type="integer")
     */
    private $obId;

    /**
     * @var int $orderNum 订单编号
     * @Column(name="order_num", type="integer", default=0)
     */
    private $orderNum;

    /**
     * @var int $orderStatus 变更类型 1:创建订单 2:订单付款 3:修改金额 4:订单取消 5:订单退款 6:订单申诉 7:卖家发货 8:确认收货 9:订单删除 10:极速到账
     * @Column(name="order_status", type="integer", default=0)
     */
    private $orderStatus;

    /**
     * @var int $recordTime 记录时间
     * @Column(name="record_time", type="integer", default=0)
     */
    private $recordTime;

    /**
     * @var int $operationTime 订单操作时间
     * @Column(name="operation_time", type="integer", default=0)
     */
    private $operationTime;

    /**
     * @var int $currentStatus 当前状态: 同步订单表status值
     * @Column(name="current_status", type="integer", default=0)
     */
    private $currentStatus;

    /**
     * @var int $currentSecStatus 当前二级状态: 同步订单表sec_status值
     * @Column(name="current_sec_status", type="integer", default=0)
     */
    private $currentSecStatus;

    /**
     * @var float $updatedPrice 状态更新后金额
     * @Column(name="updated_price", type="decimal", default=0)
     */
    private $updatedPrice;

    /**
     * @param int $value
     * @return $this
     */
    public function setObId(int $value)
    {
        $this->obId = $value;

        return $this;
    }

    /**
     * 订单编号
     * @param int $value
     * @return $this
     */
    public function setOrderNum(int $value): self
    {
        $this->orderNum = $value;

        return $this;
    }

    /**
     * 变更类型 1:创建订单 2:订单付款 3:修改金额 4:订单取消 5:订单退款 6:订单申诉 7:卖家发货 8:确认收货 9:订单删除 10:极速到账
     * @param int $value
     * @return $this
     */
    public function setOrderStatus(int $value): self
    {
        $this->orderStatus = $value;

        return $this;
    }

    /**
     * 记录时间
     * @param int $value
     * @return $this
     */
    public function setRecordTime(int $value): self
    {
        $this->recordTime = $value;

        return $this;
    }


    /**
     * 订单操作时间
     * @param int $value
     * @return $this
     */
    public function setOperationTime(int $value): self
    {
        $this->operationTime = $value;

        return $this;
    }

    /**
     * 当前状态: 同步订单表status值
     * @param int $value
     * @return $this
     */
    public function setCurrentStatus(int $value): self
    {
        $this->currentStatus = $value;

        return $this;
    }

    /**
     * 当前二级状态: 同步订单表sec_status值
     * @param int $value
     * @return $this
     */
    public function setCurrentSecStatus(int $value): self
    {
        $this->currentSecStatus = $value;

        return $this;
    }

    /**
     * 状态更新后金额
     * @param float $value
     * @return $this
     */
    public function setUpdatedPrice(float $value): self
    {
        $this->updatedPrice = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getObId()
    {
        return $this->obId;
    }

    /**
     * 订单编号
     * @return int
     */
    public function getOrderNum()
    {
        return $this->orderNum;
    }

    /**
     * 变更类型 1:创建订单 2:订单付款 3:修改金额 4:订单取消 5:订单退款 6:订单申诉 7:卖家发货 8:确认收货 9:订单删除 10:极速到账
     * @return int
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * 记录时间
     * @return int
     */
    public function getRecordTime()
    {
        return $this->recordTime;
    }

    /**
     * 订单操作时间
     * @return int
     */
    public function getOperationTime()
    {
        return $this->operationTime;
    }

    /**
     * 当前状态: 同步订单表status值
     * @return int
     */
    public function getCurrentStatus()
    {
        return $this->currentStatus;
    }

    /**
     * 当前二级状态: 同步订单表sec_status值
     * @return int
     */
    public function getCurrentSecStatus()
    {
        return $this->currentSecStatus;
    }

    /**
     * 状态更新后金额
     * @return mixed
     */
    public function getUpdatedPrice()
    {
        return $this->updatedPrice;
    }

}
