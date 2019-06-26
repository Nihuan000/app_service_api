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
 * 用户等级表
 * @Entity()
 * @Table(name="sb_user_score")
 * @uses      UserScore
 */
class UserScore extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer")
     * @Required()
     */
    private $userId;

    /**
     * @var int $scoreValue 活跃分,可以加减,目前包含发布产品及订单完成分数
     * @Column(name="score_value", type="integer")
     * @Required()
     */
    private $scoreValue;

    /**
     * @var int $baseScoreValue 基础分,包含认证过的分数,这个分数不会减少
     * @Column(name="base_score_value", type="integer")
     * @Required()
     */
    private $baseScoreValue;

    /**
     * @var int $levelId 当前等级id
     * @Column(name="level_id", type="integer")
     * @Required()
     */
    private $levelId;

    /**
     * @var string $levelName 当前等级名称
     * @Column(name="level_name", type="string", length=50, default="")
     */
    private $levelName;

    /**
     * @var int $addTime 添加时间
     * @Column(name="add_time", type="integer")
     * @Required()
     */
    private $addTime;

    /**
     * @var int $updateTime 更新时间
     * @Column(name="update_time", type="integer")
     * @Required()
     */
    private $updateTime;

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
     * 活跃分,可以加减,目前包含发布产品及订单完成分数
     * @param int $value
     * @return $this
     */
    public function setScoreValue(int $value): self
    {
        $this->scoreValue = $value;

        return $this;
    }

    /**
     * 基础分,包含认证过的分数,这个分数不会减少
     * @param int $value
     * @return $this
     */
    public function setBaseScoreValue(int $value): self
    {
        $this->baseScoreValue = $value;

        return $this;
    }

    /**
     * 当前等级id
     * @param int $value
     * @return $this
     */
    public function setLevelId(int $value): self
    {
        $this->levelId = $value;

        return $this;
    }

    /**
     * 当前等级名称
     * @param string $value
     * @return $this
     */
    public function setLevelName(string $value): self
    {
        $this->levelName = $value;

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
    public function getId()
    {
        return $this->id;
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
     * 活跃分,可以加减,目前包含发布产品及订单完成分数
     * @return int
     */
    public function getScoreValue()
    {
        return $this->scoreValue;
    }

    /**
     * 基础分,包含认证过的分数,这个分数不会减少
     * @return int
     */
    public function getBaseScoreValue()
    {
        return $this->baseScoreValue;
    }

    /**
     * 当前等级id
     * @return int
     */
    public function getLevelId()
    {
        return $this->levelId;
    }

    /**
     * 当前等级名称
     * @return string
     */
    public function getLevelName()
    {
        return $this->levelName;
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
