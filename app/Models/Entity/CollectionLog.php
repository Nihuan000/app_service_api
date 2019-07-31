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
 * 收藏日志表

 * @Entity()
 * @Table(name="sb_collection_log")
 * @uses      CollectionLog
 */
class CollectionLog extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var int $publicId 
     * @Column(name="public_id", type="integer")
     * @Required()
     */
    private $publicId;

    /**
     * @var int $type 1 采购 2产品
     * @Column(name="type", type="tinyint", default=1)
     */
    private $type;

    /**
     * @var int $userId 收藏用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $addTime 添加时间
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

    /**
     * @var int $status 1 收藏 0 取消收藏
     * @Column(name="status", type="tinyint", default=1)
     */
    private $status;

    /**
     * @param int $value
     * @return $this
     */
    public function setId(int $value)
    {
        $this->id = $value;

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setPublicId(int $value): self
    {
        $this->publicId = $value;

        return $this;
    }

    /**
     * 1 采购 2产品
     * @param int $value
     * @return $this
     */
    public function setType(int $value): self
    {
        $this->type = $value;

        return $this;
    }

    /**
     * 收藏用户id
     * @param int $value
     * @return $this
     */
    public function setUserId(int $value): self
    {
        $this->userId = $value;

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
     * 1 收藏 0 取消收藏
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPublicId()
    {
        return $this->publicId;
    }

    /**
     * 1 采购 2产品
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 收藏用户id
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
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
     * 1 收藏 0 取消收藏
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

}
