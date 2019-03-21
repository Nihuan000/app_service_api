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
 * 成长值表

 * @Entity()
 * @Table(name="sb_user_growth_rule")
 * @uses      UserGrowthRule
 */
class UserGrowthRule extends Model
{
    /**
     * @var int $id 
     * @Column(name="id", type="integer")
     * @Required()
     */
    private $id;

    /**
     * @var string $name 名称,例如：order_finished
     * @Column(name="name", type="string", length=50, default="")
     */
    private $name;

    /**
     * @var string $title 成长值标题，比如:完成一笔交易
     * @Column(name="title", type="string", length=100, default="")
     */
    private $title;

    /**
     * @var int $value 成长值分数,10为+10分,-10为减10分
     * @Column(name="value", type="integer", default=0)
     */
    private $value;

    /**
     * @var int $addTime 添加时间
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

    /**
     * @var int $addUser 添加人
     * @Column(name="add_user", type="integer", default=0)
     */
    private $addUser;

    /**
     * @var string $remark 备注
     * @Column(name="remark", type="string", length=50, default="")
     */
    private $remark;

    /**
     * @var int $userType 2:供应商 1:采购商
     * @Column(name="user_type", type="tinyint", default=0)
     */
    private $userType;

    /**
     * @var int $status 启用状态 1:启用 0：暂停
     * @Column(name="status", type="tinyint", default=0)
     */
    private $status;

    /**
     * @var int $version 记录规则版本
     * @Column(name="version", type="integer", default=0)
     */
    private $version;

    /**
     * @param int $value
     * @return $this
     */
    public function setId(int $value): self
    {
        $this->id = $value;

        return $this;
    }

    /**
     * 名称,例如：order_finished
     * @param string $value
     * @return $this
     */
    public function setName(string $value): self
    {
        $this->name = $value;

        return $this;
    }

    /**
     * 成长值标题，比如:完成一笔交易
     * @param string $value
     * @return $this
     */
    public function setTitle(string $value): self
    {
        $this->title = $value;

        return $this;
    }

    /**
     * 成长值分数,10为+10分,-10为减10分
     * @param int $value
     * @return $this
     */
    public function setValue(int $value): self
    {
        $this->value = $value;

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
     * 备注
     * @param string $value
     * @return $this
     */
    public function setRemark(string $value): self
    {
        $this->remark = $value;

        return $this;
    }

    /**
     * 2:供应商 1:采购商
     * @param int $value
     * @return $this
     */
    public function setUserType(int $value): self
    {
        $this->userType = $value;

        return $this;
    }

    /**
     * 启用状态 1:启用 0：暂停
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * 记录规则版本
     * @param int $value
     * @return $this
     */
    public function setVersion(int $value): self
    {
        $this->version = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 名称,例如：order_finished
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 成长值标题，比如:完成一笔交易
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * 成长值分数,10为+10分,-10为减10分
     * @return int
     */
    public function getValue()
    {
        return $this->value;
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
     * 备注
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * 2:供应商 1:采购商
     * @return int
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * 启用状态 1:启用 0：暂停
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 记录规则版本
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

}
