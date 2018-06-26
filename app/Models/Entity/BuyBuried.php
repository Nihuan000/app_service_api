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
 * 采购动态日志表
 * @Entity()
 * @Table(name="sb_buy_buried_log")
 * @uses      BuyBuried
 */
class BuyBuried extends Model
{
    /**
     * @var int $bbId 
     * @Id()
     * @Column(name="bb_id", type="integer")
     */
    private $bbId;

    /**
     * @var int $buyId 采购id
     * @Column(name="buy_id", type="integer", default=0)
     */
    private $buyId;

    /**
     * @var int $buyStatus 采购状态 1:发布采购 2:修改采购 4:删除采购
     * @Column(name="buy_status", type="tinyint", default=0)
     */
    private $buyStatus;

    /**
     * @var int $findStatus 找布状态: 1: 寻找中 2: 报价找到 3:搜索产品找到 4:线下找到 5:其他方式找到 6:不找了
     * @Column(name="find_status", type="tinyint", default=0)
     */
    private $findStatus;

    /**
     * @var int $operationTime 操作时间
     * @Column(name="operation_time", type="integer", default=0)
     */
    private $operationTime;

    /**
     * @var int $recordTime 记录时间
     * @Column(name="record_time", type="integer", default=0)
     */
    private $recordTime;

    /**
     * @param int $value
     * @return $this
     */
    public function setBbId(int $value)
    {
        $this->bbId = $value;

        return $this;
    }

    /**
     * 采购id
     * @param int $value
     * @return $this
     */
    public function setBuyId(int $value): self
    {
        $this->buyId = $value;

        return $this;
    }

    /**
     * 采购状态 1:发布采购 2:修改采购 4:删除采购
     * @param int $value
     * @return $this
     */
    public function setBuyStatus(int $value): self
    {
        $this->buyStatus = $value;

        return $this;
    }

    /**
     * 找布状态: 1: 寻找中 2: 报价找到 3:搜索产品找到 4:线下找到 5:其他方式找到 6:不找了
     * @param int $value
     * @return $this
     */
    public function setFindStatus(int $value): self
    {
        $this->findStatus = $value;

        return $this;
    }

    /**
     * 开始找布时间
     * @param int $value
     * @return $this
     */
    public function setOperationTime(int $value): self
    {
        $this->operationTime = $value;

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
     * @return mixed
     */
    public function getBbId()
    {
        return $this->bbId;
    }

    /**
     * 采购id
     * @return int
     */
    public function getBuyId()
    {
        return $this->buyId;
    }

    /**
     * 采购状态 1:发布采购 2:修改采购 4:删除采购
     * @return int
     */
    public function getBuyStatus()
    {
        return $this->buyStatus;
    }

    /**
     * 找布状态: 1: 寻找中 2: 报价找到 3:搜索产品找到 4:线下找到 5:其他方式找到 6:不找了
     * @return int
     */
    public function getFindStatus()
    {
        return $this->findStatus;
    }

    /**
     * 操作时间
     * @return int
     */
    public function getOperationTime()
    {
        return $this->operationTime;
    }


    /**
     * 记录时间
     * @return int
     */
    public function getRecordTime()
    {
        return $this->recordTime;
    }

}
