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
 * 供应商数据报表
 * @Entity()
 * @Table(name="sb_supplier_data_statistic")
 * @uses      SupplierDataStatistic
 */
class SupplierDataStatistic extends Model
{
    /**
     * @var int $sdsId 
     * @Id()
     * @Column(name="sds_id", type="integer")
     */
    private $sdsId;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $daysType 统计天数
     * @Column(name="days_type", type="tinyint", default=0)
     */
    private $daysType;

    /**
     * @var int $loginDays 登录天数
     * @Column(name="login_days", type="integer", default=0)
     */
    private $loginDays;

    /**
     * @var int $unreadCount 未登录时订阅采购发布数
     * @Column(name="unread_count", type="integer", default=0)
     */
    private $unreadCount;

    /**
     * @var float $avgReplySec 平均回复时长
     * @Column(name="avg_reply_sec", type="float", default=0)
     */
    private $avgReplySec;

    /**
     * @var int $unReplyCount 未回复消息数
     * @Column(name="un_reply_count", type="integer", default=0)
     */
    private $unReplyCount;

    /**
     * @var int $unReplyVisit 未沟通访客数
     * @Column(name="un_reply_visit", type="integer", default=0)
     */
    private $unReplyVisit;

    /**
     * @var int $totalVisitCount 访客数
     * @Column(name="total_visit_count", type="integer", default=0)
     */
    private $totalVisitCount;

    /**
     * @var int $sendStatus 发送状态 0:未发送 1:已发送 -1无需发送
     * @Column(name="send_status", type="tinyint", default=0)
     */
    private $sendStatus;

    /**
     * @var int $recordTime 记录时间
     * @Column(name="record_time", type="integer", default=0)
     */
    private $recordTime;

    /**
     * @var int $sendTime 发送时间
     * @Column(name="send_time", type="integer", default=0)
     */
    private $sendTime;

    /**
     * @param int $value
     * @return $this
     */
    public function setSdsId(int $value)
    {
        $this->sdsId = $value;

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
     * 统计天数
     * @param int $value
     * @return $this
     */
    public function setDaysType(int $value): self
    {
        $this->daysType = $value;

        return $this;
    }

    /**
     * 登录天数
     * @param int $value
     * @return $this
     */
    public function setLoginDays(int $value): self
    {
        $this->loginDays = $value;

        return $this;
    }

    /**
     * 未登录时订阅采购发布数
     * @param int $value
     * @return $this
     */
    public function setUnreadCount(int $value): self
    {
        $this->unreadCount = $value;

        return $this;
    }

    /**
     * 平均回复时长
     * @param float $value
     * @return $this
     */
    public function setAvgReplySec(float $value): self
    {
        $this->avgReplySec = $value;

        return $this;
    }

    /**
     * 未回复消息数
     * @param int $value
     * @return $this
     */
    public function setUnReplyCount(int $value): self
    {
        $this->unReplyCount = $value;

        return $this;
    }

    /**
     * 未沟通访客数
     * @param int $value
     * @return $this
     */
    public function setUnReplyVisit(int $value): self
    {
        $this->unReplyVisit = $value;

        return $this;
    }

    /**
     * 访客数
     * @param int $value
     * @return $this
     */
    public function setTotalVisitCount(int $value): self
    {
        $this->totalVisitCount = $value;

        return $this;
    }

    /**
     * 发送状态 0:未发送 1:已发送 -1无需发送
     * @param int $value
     * @return $this
     */
    public function setSendStatus(int $value): self
    {
        $this->sendStatus = $value;

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
     * 发送时间
     * @param int $value
     * @return $this
     */
    public function setSendTime(int $value): self
    {
        $this->sendTime = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSdsId()
    {
        return $this->sdsId;
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
     * 统计天数
     * @return int
     */
    public function getDaysType()
    {
        return $this->daysType;
    }

    /**
     * 登录天数
     * @return int
     */
    public function getLoginDays()
    {
        return $this->loginDays;
    }

    /**
     * 未登录时订阅采购发布数
     * @return int
     */
    public function getUnreadCount()
    {
        return $this->unreadCount;
    }

    /**
     * 平均回复时长
     * @return float
     */
    public function getAvgReplySec()
    {
        return $this->avgReplySec;
    }

    /**
     * 未回复消息数
     * @return int
     */
    public function getUnReplyCount()
    {
        return $this->unReplyCount;
    }

    /**
     * 未沟通访客数
     * @return int
     */
    public function getUnReplyVisit()
    {
        return $this->unReplyVisit;
    }

    /**
     * 访客数
     * @return int
     */
    public function getTotalVisitCount()
    {
        return $this->totalVisitCount;
    }

    /**
     * 发送状态 0:未发送 1:已发送 -1无需发送
     * @return int
     */
    public function getSendStatus()
    {
        return $this->sendStatus;
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
     * 发送时间
     * @return int
     */
    public function getSendTime()
    {
        return $this->sendTime;
    }

}
