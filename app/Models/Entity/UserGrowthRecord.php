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
 * 用户成长值记录表

 * @Entity()
 * @Table(name="sb_user_growth_record")
 * @uses      UserGrowthRecord
 */
class UserGrowthRecord extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var int $userId 用户主键值
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $growthId 成长值id
     * @Column(name="growth_id", type="integer", default=0)
     */
    private $growthId;

    /**
     * @var int $growth 成长值变化值
     * @Column(name="growth", type="integer", default=0)
     */
    private $growth;

    /**
     * @var string $name 成长值标识符
     * @Column(name="name", type="string", length=100, default="")
     */
    private $name;

    /**
     * @var string $title 标题
     * @Column(name="title", type="string", length=100, default="")
     */
    private $title;

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
     * @var string $remark 备注信息
     * @Column(name="remark", type="string", length=255, default="")
     */
    private $remark;

    /**
     * @var int $version 规则版本号
     * @Column(name="version", type="integer", default=0)
     */
    private $version;

    /**
     * @var int $status 状态 1:有效记录 2：无效记录
     * @Column(name="status", type="tinyint", default=1)
     */
    private $status;

    /**
     * @var int $operateId 如果有数值表示后台管理员添加
     * @Column(name="operate_id", type="integer", default=0)
     */
    private $operateId;

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
     * 用户主键值
     * @param int $value
     * @return $this
     */
    public function setUserId(int $value): self
    {
        $this->userId = $value;

        return $this;
    }

    /**
     * 成长值id
     * @param int $value
     * @return $this
     */
    public function setGrowthId(int $value): self
    {
        $this->growthId = $value;

        return $this;
    }

    /**
     * 成长值变化值
     * @param int $value
     * @return $this
     */
    public function setGrowth(int $value): self
    {
        $this->growth = $value;

        return $this;
    }

    /**
     * 成长值标识符
     * @param string $value
     * @return $this
     */
    public function setName(string $value): self
    {
        $this->name = $value;

        return $this;
    }

    /**
     * 标题
     * @param string $value
     * @return $this
     */
    public function setTitle(string $value): self
    {
        $this->title = $value;

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
     * 备注信息
     * @param string $value
     * @return $this
     */
    public function setRemark(string $value): self
    {
        $this->remark = $value;

        return $this;
    }

    /**
     * 规则版本号
     * @param int $value
     * @return $this
     */
    public function setVersion(int $value): self
    {
        $this->version = $value;

        return $this;
    }

    /**
     * 状态 1:有效记录 2：无效记录
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * 如果有数值表示后台管理员添加
     * @param int $value
     * @return $this
     */
    public function setOperateId(int $value): self
    {
        $this->operateId = $value;

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
     * 用户主键值
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 成长值id
     * @return int
     */
    public function getGrowthId()
    {
        return $this->growthId;
    }

    /**
     * 成长值变化值
     * @return int
     */
    public function getGrowth()
    {
        return $this->growth;
    }

    /**
     * 成长值标识符
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 标题
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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

    /**
     * 备注信息
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * 规则版本号
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * 状态 1:有效记录 2：无效记录
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 如果有数值表示后台管理员添加
     * @return int
     */
    public function getOperateId()
    {
        return $this->operateId;
    }

}
