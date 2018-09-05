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
 * 采购有效期设置日志表
 * @Entity()
 * @Table(name="sb_buy_ffective_log")
 * @uses      BuyFfectiveLog
 */
class BuyFfectiveLog extends Model
{
    /**
     * @var int $bflId 
     * @Id()
     * @Column(name="bfl_id", type="integer")
     */
    private $bflId;

    /**
     * @var int $buyId 采购id
     * @Column(name="buy_id", type="integer", default=0)
     */
    private $buyId;

    /**
     * @var int $ffectiveDate 有效期（天）
     * @Column(name="ffective_date", type="integer", default=0)
     */
    private $ffectiveDate;

    /**
     * @var int $startTime 开始时间
     * @Column(name="start_time", type="integer", default=0)
     */
    private $startTime;

    /**
     * @var int $endTime 结束时间
     * @Column(name="end_time", type="integer", default=0)
     */
    private $endTime;

    /**
     * @var int $status 执行状态 0：待执行（采购信息后台审核） 1:已执行
     * @Column(name="status", type="tinyint", default=0)
     */
    private $status;

    /**
     * @var int $recordTime 记录时间
     * @Column(name="record_time", type="integer", default=0)
     */
    private $recordTime;

    /**
     * @param int $value
     * @return $this
     */
    public function setBflId(int $value)
    {
        $this->bflId = $value;

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
     * 有效期（天）
     * @param int $value
     * @return $this
     */
    public function setFfectiveDate(int $value): self
    {
        $this->ffectiveDate = $value;

        return $this;
    }

    /**
     * 开始时间
     * @param int $value
     * @return $this
     */
    public function setStartTime(int $value): self
    {
        $this->startTime = $value;

        return $this;
    }

    /**
     * 结束时间
     * @param int $value
     * @return $this
     */
    public function setEndTime(int $value): self
    {
        $this->endTime = $value;

        return $this;
    }

    /**
     * 执行状态 0：待执行（采购信息后台审核） 1:已执行
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

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
    public function getBflId()
    {
        return $this->bflId;
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
     * 有效期（天）
     * @return int
     */
    public function getFfectiveDate()
    {
        return $this->ffectiveDate;
    }

    /**
     * 开始时间
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * 结束时间
     * @return int
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * 执行状态 0：待执行（采购信息后台审核） 1:已执行
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
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
