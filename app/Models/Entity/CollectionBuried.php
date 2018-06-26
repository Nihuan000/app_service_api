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
 * 收藏动态日志表
 * @Entity()
 * @Table(name="sb_collection_buried_log")
 * @uses      CollectionBuried
 */
class CollectionBuried extends Model
{
    /**
     * @var int $cblId 
     * @Id()
     * @Column(name="cbl_id", type="integer")
     */
    private $cblId;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $collectType 收藏类型 1:产品 2:采购
     * @Column(name="collect_type", type="tinyint", default=0)
     */
    private $collectType;

    /**
     * @var int $publicId 采购/产品id
     * @Column(name="public_id", type="integer", default=0)
     */
    private $publicId;

    /**
     * @var int $collectStatus 收藏状态 1: 已收藏 2:取消收藏
     * @Column(name="collect_status", type="tinyint", default=1)
     */
    private $collectStatus;

    /**
     * @var int $recordTime 记录时间
     * @Column(name="record_time", type="integer", default=0)
     */
    private $recordTime;

    /**
     * @param int $value
     * @return $this
     */
    public function setCblId(int $value)
    {
        $this->cblId = $value;

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
     * 收藏类型 1:产品 2:采购
     * @param int $value
     * @return $this
     */
    public function setCollectType(int $value): self
    {
        $this->collectType = $value;

        return $this;
    }

    /**
     * 采购/产品id
     * @param int $value
     * @return $this
     */
    public function setPublicId(int $value): self
    {
        $this->publicId = $value;

        return $this;
    }

    /**
     * 收藏状态 1: 已收藏 2:取消收藏
     * @param int $value
     * @return $this
     */
    public function setCollectStatus(int $value): self
    {
        $this->collectStatus = $value;

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
    public function getCblId()
    {
        return $this->cblId;
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
     * 收藏类型 1:产品 2:采购
     * @return int
     */
    public function getCollectType()
    {
        return $this->collectType;
    }

    /**
     * 采购/产品id
     * @return int
     */
    public function getPublicId()
    {
        return $this->publicId;
    }

    /**
     * 收藏状态 1: 已收藏 2:取消收藏
     * @return mixed
     */
    public function getCollectStatus()
    {
        return $this->collectStatus;
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
