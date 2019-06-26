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
 * 用户积分等级表
 * @Entity()
 * @Table(name="sb_user_score_level_rule")
 * @uses      UserScoreLevelRule
 */
class UserScoreLevelRule extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var string $levelName 金银铜
     * @Column(name="level_name", type="string", length=50, default="")
     */
    private $levelName;

    /**
     * @var string $levelDesc 层级描述
     * @Column(name="level_desc", type="string", length=255, default="")
     */
    private $levelDesc;

    /**
     * @var int $userType 1:供应商 2:采购
     * @Column(name="user_type", type="integer")
     * @Required()
     */
    private $userType;

    /**
     * @var int $minScore 最小积分制(活跃分+基础分)
     * @Column(name="min_score", type="integer")
     * @Required()
     */
    private $minScore;

    /**
     * @var int $minProductScore 最小产品活跃分
     * @Column(name="min_product_score", type="integer")
     * @Required()
     */
    private $minProductScore;

    /**
     * @var int $minOrderScore 最小订单活跃分
     * @Column(name="min_order_score", type="integer")
     * @Required()
     */
    private $minOrderScore;

    /**
     * @var int $sort 排序
     * @Column(name="sort", type="smallint")
     * @Required()
     */
    private $sort;

    /**
     * @var int $addTime 添加时间
     * @Column(name="add_time", type="integer")
     * @Required()
     */
    private $addTime;

    /**
     * @var int $addUser 添加人
     * @Column(name="add_user", type="integer")
     * @Required()
     */
    private $addUser;

    /**
     * @var int $updateTime 修改时间
     * @Column(name="update_time", type="integer")
     * @Required()
     */
    private $updateTime;

    /**
     * @var int $updateUser 修改人
     * @Column(name="update_user", type="integer")
     * @Required()
     */
    private $updateUser;

    /**
     * @var int $isDowngrade 是否允许降级 1:允许降级 0:不允许降级(目前只适用于铜牌)
     * @Column(name="is_downgrade", type="tinyint", default=1)
     */
    private $isDowngrade;

    /**
     * @var int $startTime 等级规则有效期开始时间
     * @Column(name="start_time", type="integer")
     * @Required()
     */
    private $startTime;

    /**
     * @var int $endTime 等级规则有效期结束时间
     * @Column(name="end_time", type="integer")
     * @Required()
     */
    private $endTime;

    /**
     * @var int $isEnable 是否启用 1:启用 2:不启用
     * @Column(name="is_enable", type="tinyint", default=1)
     */
    private $isEnable;

    /**
     * @var int $isDelete 是否删除 1:删除  0:不删除
     * @Column(name="is_delete", type="tinyint")
     * @Required()
     */
    private $isDelete;

    /**
     * @var int $isPay 是否付费
     * @Column(name="is_pay", type="tinyint")
     * @Required()
     */
    private $isPay;

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
     * 金银铜
     * @param string $value
     * @return $this
     */
    public function setLevelName(string $value): self
    {
        $this->levelName = $value;

        return $this;
    }

    /**
     * 层级描述
     * @param string $value
     * @return $this
     */
    public function setLevelDesc(string $value): self
    {
        $this->levelDesc = $value;

        return $this;
    }

    /**
     * 1:供应商 2:采购
     * @param int $value
     * @return $this
     */
    public function setUserType(int $value): self
    {
        $this->userType = $value;

        return $this;
    }

    /**
     * 最小积分制(活跃分+基础分)
     * @param int $value
     * @return $this
     */
    public function setMinScore(int $value): self
    {
        $this->minScore = $value;

        return $this;
    }

    /**
     * 最小产品活跃分
     * @param int $value
     * @return $this
     */
    public function setMinProductScore(int $value): self
    {
        $this->minProductScore = $value;

        return $this;
    }

    /**
     * 最小订单活跃分
     * @param int $value
     * @return $this
     */
    public function setMinOrderScore(int $value): self
    {
        $this->minOrderScore = $value;

        return $this;
    }

    /**
     * 排序
     * @param int $value
     * @return $this
     */
    public function setSort(int $value): self
    {
        $this->sort = $value;

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
     * 添加人
     * @param int $value
     * @return $this
     */
    public function setAddUser(int $value): self
    {
        $this->addUser = $value;

        return $this;
    }

    /**
     * 修改时间
     * @param int $value
     * @return $this
     */
    public function setUpdateTime(int $value): self
    {
        $this->updateTime = $value;

        return $this;
    }

    /**
     * 修改人
     * @param int $value
     * @return $this
     */
    public function setUpdateUser(int $value): self
    {
        $this->updateUser = $value;

        return $this;
    }

    /**
     * 是否允许降级 1:允许降级 0:不允许降级(目前只适用于铜牌)
     * @param int $value
     * @return $this
     */
    public function setIsDowngrade(int $value): self
    {
        $this->isDowngrade = $value;

        return $this;
    }

    /**
     * 等级规则有效期开始时间
     * @param int $value
     * @return $this
     */
    public function setStartTime(int $value): self
    {
        $this->startTime = $value;

        return $this;
    }

    /**
     * 等级规则有效期结束时间
     * @param int $value
     * @return $this
     */
    public function setEndTime(int $value): self
    {
        $this->endTime = $value;

        return $this;
    }

    /**
     * 是否启用 1:启用 2:不启用
     * @param int $value
     * @return $this
     */
    public function setIsEnable(int $value): self
    {
        $this->isEnable = $value;

        return $this;
    }

    /**
     * 是否删除 1:删除  0:不删除
     * @param int $value
     * @return $this
     */
    public function setIsDelete(int $value): self
    {
        $this->isDelete = $value;

        return $this;
    }

    /**
     * 是否付费
     * @param int $value
     * @return $this
     */
    public function setIsPay(int $value): self
    {
        $this->isPay = $value;

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
     * 金银铜
     * @return string
     */
    public function getLevelName()
    {
        return $this->levelName;
    }

    /**
     * 层级描述
     * @return string
     */
    public function getLevelDesc()
    {
        return $this->levelDesc;
    }

    /**
     * 1:供应商 2:采购
     * @return int
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * 最小积分制(活跃分+基础分)
     * @return int
     */
    public function getMinScore()
    {
        return $this->minScore;
    }

    /**
     * 最小产品活跃分
     * @return int
     */
    public function getMinProductScore()
    {
        return $this->minProductScore;
    }

    /**
     * 最小订单活跃分
     * @return int
     */
    public function getMinOrderScore()
    {
        return $this->minOrderScore;
    }

    /**
     * 排序
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
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
     * 添加人
     * @return int
     */
    public function getAddUser()
    {
        return $this->addUser;
    }

    /**
     * 修改时间
     * @return int
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    /**
     * 修改人
     * @return int
     */
    public function getUpdateUser()
    {
        return $this->updateUser;
    }

    /**
     * 是否允许降级 1:允许降级 0:不允许降级(目前只适用于铜牌)
     * @return mixed
     */
    public function getIsDowngrade()
    {
        return $this->isDowngrade;
    }

    /**
     * 等级规则有效期开始时间
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * 等级规则有效期结束时间
     * @return int
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * 是否启用 1:启用 2:不启用
     * @return mixed
     */
    public function getIsEnable()
    {
        return $this->isEnable;
    }

    /**
     * 是否删除 1:删除  0:不删除
     * @return int
     */
    public function getIsDelete()
    {
        return $this->isDelete;
    }

    /**
     * 是否付费
     * @return int
     */
    public function getIsPay()
    {
        return $this->isPay;
    }

}
