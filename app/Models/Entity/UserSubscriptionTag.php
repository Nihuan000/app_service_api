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
 * 用户订阅标签表

 * @Entity()
 * @Table(name="sb_user_subscription_tag")
 * @uses      UserSubscriptionTag
 */
class UserSubscriptionTag extends Model
{
    /**
     * @var int $userTagId 
     * @Id()
     * @Column(name="user_tag_id", type="integer")
     */
    private $userTagId;

    /**
     * @var int $userId 用户ID 
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $tagId 标签id
     * @Column(name="tag_id", type="integer", default=0)
     */
    private $tagId;

    /**
     * @var string $tagName 标签名称
     * @Column(name="tag_name", type="string", length=55, default="")
     */
    private $tagName;

    /**
     * @var int $parentId 父类ID
     * @Column(name="parent_id", type="integer", default=0)
     */
    private $parentId;

    /**
     * @var string $parentName 父级标签名称
     * @Column(name="parent_name", type="string", length=55, default="")
     */
    private $parentName;

    /**
     * @var int $topId 顶级类ID
     * @Column(name="top_id", type="integer", default=0)
     */
    private $topId;

    /**
     * @var string $topName 顶级类标签名称
     * @Column(name="top_name", type="string", length=55, default="")
     */
    private $topName;

    /**
     * @var int $markTimes 标记不匹配次数
     * @Column(name="mark_times", type="tinyint", default=0)
     */
    private $markTimes;

    /**
     * @var int $addTime 添加时间
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

    /**
     * @var int $updateTime 更新时间
     * @Column(name="update_time", type="integer", default=0)
     */
    private $updateTime;

    /**
     * @param int $value
     * @return $this
     */
    public function setUserTagId(int $value)
    {
        $this->userTagId = $value;

        return $this;
    }

    /**
     * 用户ID 
     * @param int $value
     * @return $this
     */
    public function setUserId(int $value): self
    {
        $this->userId = $value;

        return $this;
    }

    /**
     * 标签id
     * @param int $value
     * @return $this
     */
    public function setTagId(int $value): self
    {
        $this->tagId = $value;

        return $this;
    }

    /**
     * 标签名称
     * @param string $value
     * @return $this
     */
    public function setTagName(string $value): self
    {
        $this->tagName = $value;

        return $this;
    }

    /**
     * 父类ID
     * @param int $value
     * @return $this
     */
    public function setParentId(int $value): self
    {
        $this->parentId = $value;

        return $this;
    }

    /**
     * 父级标签名称
     * @param string $value
     * @return $this
     */
    public function setParentName(string $value): self
    {
        $this->parentName = $value;

        return $this;
    }

    /**
     * 顶级类ID
     * @param int $value
     * @return $this
     */
    public function setTopId(int $value): self
    {
        $this->topId = $value;

        return $this;
    }

    /**
     * 顶级类标签名称
     * @param string $value
     * @return $this
     */
    public function setTopName(string $value): self
    {
        $this->topName = $value;

        return $this;
    }

    /**
     * 标记不匹配次数
     * @param int $value
     * @return $this
     */
    public function setMarkTimes(int $value): self
    {
        $this->markTimes = $value;

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
     * 更新时间
     * @param int $value
     * @return $this
     */
    public function setUpdateTime(int $value): self
    {
        $this->updateTime = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserTagId()
    {
        return $this->userTagId;
    }

    /**
     * 用户ID 
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 标签id
     * @return int
     */
    public function getTagId()
    {
        return $this->tagId;
    }

    /**
     * 标签名称
     * @return string
     */
    public function getTagName()
    {
        return $this->tagName;
    }

    /**
     * 父类ID
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * 父级标签名称
     * @return string
     */
    public function getParentName()
    {
        return $this->parentName;
    }

    /**
     * 顶级类ID
     * @return int
     */
    public function getTopId()
    {
        return $this->topId;
    }

    /**
     * 顶级类标签名称
     * @return string
     */
    public function getTopName()
    {
        return $this->topName;
    }

    /**
     * 标记不匹配次数
     * @return int
     */
    public function getMarkTimes()
    {
        return $this->markTimes;
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
     * 更新时间
     * @return int
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

}
