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
 * 用户等级变更记录表
 * @Entity()
 * @Table(name="sb_user_score_level_update_record")
 * @uses      UserScoreLevelUpdateRecord
 */
class UserScoreLevelUpdateRecord extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var int $scoreGetRecordId sb_user_score_get_record 主键值,0:为脚本跑的
     * @Column(name="score_get_record_id", type="integer", default=0)
     */
    private $scoreGetRecordId;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $oldScore 
     * @Column(name="old_score", type="integer", default=0)
     */
    private $oldScore;

    /**
     * @var int $oldLevelId sb_user_score_level_rule 主键值
     * @Column(name="old_level_id", type="integer", default=0)
     */
    private $oldLevelId;

    /**
     * @var string $oldLevelName 旧等级名称
     * @Column(name="old_level_name", type="string", length=50, default="")
     */
    private $oldLevelName;

    /**
     * @var int $newScore 活跃分+基础分
     * @Column(name="new_score", type="integer", default=0)
     */
    private $newScore;

    /**
     * @var int $newLevelId 新等级id
     * @Column(name="new_level_id", type="integer", default=0)
     */
    private $newLevelId;

    /**
     * @var string $newLevelName 新等级名称
     * @Column(name="new_level_name", type="string", length=50, default="")
     */
    private $newLevelName;

    /**
     * @var int $optUserId 操作人id 0:系统脚本
     * @Column(name="opt_user_id", type="integer", default=0)
     */
    private $optUserId;

    /**
     * @var int $optUserType 操作人类型 1:前台用户 2:后台管理员 3:脚本
     * @Column(name="opt_user_type", type="integer", default=0)
     */
    private $optUserType;

    /**
     * @var int $isAuto 是否自动升降级 1:是 0:否
     * @Column(name="is_auto", type="tinyint", default=1)
     */
    private $isAuto;

    /**
     * @var string $desc 
     * @Column(name="desc", type="string", length=255, default="")
     */
    private $desc;

    /**
     * @var int $addTime 添加名称
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

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
     * sb_user_score_get_record 主键值,0:为脚本跑的
     * @param int $value
     * @return $this
     */
    public function setScoreGetRecordId(int $value): self
    {
        $this->scoreGetRecordId = $value;

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
     * @param int $value
     * @return $this
     */
    public function setOldScore(int $value): self
    {
        $this->oldScore = $value;

        return $this;
    }

    /**
     * sb_user_score_level_rule 主键值
     * @param int $value
     * @return $this
     */
    public function setOldLevelId(int $value): self
    {
        $this->oldLevelId = $value;

        return $this;
    }

    /**
     * 旧等级名称
     * @param string $value
     * @return $this
     */
    public function setOldLevelName(string $value): self
    {
        $this->oldLevelName = $value;

        return $this;
    }

    /**
     * 活跃分+基础分
     * @param int $value
     * @return $this
     */
    public function setNewScore(int $value): self
    {
        $this->newScore = $value;

        return $this;
    }

    /**
     * 新等级id
     * @param int $value
     * @return $this
     */
    public function setNewLevelId(int $value): self
    {
        $this->newLevelId = $value;

        return $this;
    }

    /**
     * 新等级名称
     * @param string $value
     * @return $this
     */
    public function setNewLevelName(string $value): self
    {
        $this->newLevelName = $value;

        return $this;
    }

    /**
     * 操作人id 0:系统脚本
     * @param int $value
     * @return $this
     */
    public function setOptUserId(int $value): self
    {
        $this->optUserId = $value;

        return $this;
    }

    /**
     * 操作人类型 1:前台用户 2:后台管理员 3:脚本
     * @param int $value
     * @return $this
     */
    public function setOptUserType(int $value): self
    {
        $this->optUserType = $value;

        return $this;
    }

    /**
     * 是否自动升降级 1:是 0:否
     * @param int $value
     * @return $this
     */
    public function setIsAuto(int $value): self
    {
        $this->isAuto = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDesc(string $value): self
    {
        $this->desc = $value;

        return $this;
    }

    /**
     * 添加名称
     * @param int $value
     * @return $this
     */
    public function setAddTime(int $value): self
    {
        $this->addTime = $value;

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
     * sb_user_score_get_record 主键值,0:为脚本跑的
     * @return int
     */
    public function getScoreGetRecordId()
    {
        return $this->scoreGetRecordId;
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
     * @return int
     */
    public function getOldScore()
    {
        return $this->oldScore;
    }

    /**
     * sb_user_score_level_rule 主键值
     * @return int
     */
    public function getOldLevelId()
    {
        return $this->oldLevelId;
    }

    /**
     * 旧等级名称
     * @return string
     */
    public function getOldLevelName()
    {
        return $this->oldLevelName;
    }

    /**
     * 活跃分+基础分
     * @return int
     */
    public function getNewScore()
    {
        return $this->newScore;
    }

    /**
     * 新等级id
     * @return int
     */
    public function getNewLevelId()
    {
        return $this->newLevelId;
    }

    /**
     * 新等级名称
     * @return string
     */
    public function getNewLevelName()
    {
        return $this->newLevelName;
    }

    /**
     * 操作人id 0:系统脚本
     * @return int
     */
    public function getOptUserId()
    {
        return $this->optUserId;
    }

    /**
     * 操作人类型 1:前台用户 2:后台管理员 3:脚本
     * @return int
     */
    public function getOptUserType()
    {
        return $this->optUserType;
    }

    /**
     * 是否自动升降级 1:是 0:否
     * @return mixed
     */
    public function getIsAuto()
    {
        return $this->isAuto;
    }

    /**
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * 添加名称
     * @return int
     */
    public function getAddTime()
    {
        return $this->addTime;
    }

}
