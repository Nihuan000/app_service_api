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
 * 实力商家表
 * @Entity()
 * @Table(name="sb_user_strength")
 * @uses      UserStrength
 */
class UserStrength extends Model
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
     * @var int $startTime 开始时间
     * @Column(name="start_time", type="integer")
     * @Required()
     */
    private $startTime;

    /**
     * @var int $endTime 结束时间
     * @Column(name="end_time", type="integer")
     * @Required()
     */
    private $endTime;

    /**
     * @var int $isExpire 是否过期
     * @Column(name="is_expire", type="tinyint", default=0)
     */
    private $isExpire;

    /**
     * @var float $serviceFeeRate 服务费比例 0-1之间 0:不收费服务费 0.1:10%的服务费 1:100%的服务费
     * @Column(name="service_fee_rate", type="float")
     * @Required()
     */
    private $serviceFeeRate;

    /**
     * @var int $level 实力商家等级 5:1888充值的实力商家 10:4888充值的实力商家
     * @Column(name="level", type="smallint")
     * @Required()
     */
    private $level;

    /**
     * @var int $payForOpen 是否付费开通　0:否　１：是
     * @Column(name="pay_for_open", type="tinyint", default=0)
     */
    private $payForOpen;

    /**
     * @var int $areaGroup 区域分组 1:客服部 2 :广州区 3：柯桥区
     * @Column(name="area_group", type="integer", default=0)
     */
    private $areaGroup;

    /**
     * @var int $addTime 添加时间
     * @Column(name="add_time", type="integer")
     * @Required()
     */
    private $addTime;

    /**
     * @var int $updateTime 修改时间
     * @Column(name="update_time", type="integer")
     * @Required()
     */
    private $updateTime;

    /**
     * @var string $remark 备注
     * @Column(name="remark", type="string", length=255, default="")
     */
    private $remark;

    /**
     * @var int $renewTime 续费开始时间
     * @Column(name="renew_time", type="integer", default=0)
     */
    private $renewTime;

    /**
     * @var int $renewPayTime 续费付款时间
     * @Column(name="renew_pay_time", type="integer", default=0)
     */
    private $renewPayTime;

    /**
     * @var float $totalAmount 交易总额
     * @Column(name="total_amount", type="decimal", default=0)
     */
    private $totalAmount;

    /**
     * @var float $orderThreshold 交易额度阈值
     * @Column(name="order_threshold", type="decimal", default=300000)
     */
    private $orderThreshold;

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
     * 是否过期
     * @param int $value
     * @return $this
     */
    public function setIsExpire(int $value): self
    {
        $this->isExpire = $value;

        return $this;
    }

    /**
     * 服务费比例 0-1之间 0:不收费服务费 0.1:10%的服务费 1:100%的服务费
     * @param float $value
     * @return $this
     */
    public function setServiceFeeRate(float $value): self
    {
        $this->serviceFeeRate = $value;

        return $this;
    }

    /**
     * 实力商家等级 5:1888充值的实力商家 10:4888充值的实力商家
     * @param int $value
     * @return $this
     */
    public function setLevel(int $value): self
    {
        $this->level = $value;

        return $this;
    }

    /**
     * 是否付费开通　0:否　１：是
     * @param int $value
     * @return $this
     */
    public function setPayForOpen(int $value): self
    {
        $this->payForOpen = $value;

        return $this;
    }

    /**
     * 区域分组 1:客服部 2 :广州区 3：柯桥区
     * @param int $value
     * @return $this
     */
    public function setAreaGroup(int $value): self
    {
        $this->areaGroup = $value;

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
     * 续费开始时间
     * @param int $value
     * @return $this
     */
    public function setRenewTime(int $value): self
    {
        $this->renewTime = $value;

        return $this;
    }

    /**
     * 续费付款时间
     * @param int $value
     * @return $this
     */
    public function setRenewPayTime(int $value): self
    {
        $this->renewPayTime = $value;

        return $this;
    }

    /**
     * 交易总额
     * @param float $value
     * @return $this
     */
    public function setTotalAmount(float $value): self
    {
        $this->totalAmount = $value;

        return $this;
    }

    /**
     * 交易额度阈值
     * @param float $value
     * @return $this
     */
    public function setOrderThreshold(float $value): self
    {
        $this->orderThreshold = $value;

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
     * 是否过期
     * @return int
     */
    public function getIsExpire()
    {
        return $this->isExpire;
    }

    /**
     * 服务费比例 0-1之间 0:不收费服务费 0.1:10%的服务费 1:100%的服务费
     * @return float
     */
    public function getServiceFeeRate()
    {
        return $this->serviceFeeRate;
    }

    /**
     * 实力商家等级 5:1888充值的实力商家 10:4888充值的实力商家
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * 是否付费开通　0:否　１：是
     * @return int
     */
    public function getPayForOpen()
    {
        return $this->payForOpen;
    }

    /**
     * 区域分组 1:客服部 2 :广州区 3：柯桥区
     * @return int
     */
    public function getAreaGroup()
    {
        return $this->areaGroup;
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
     * 修改时间
     * @return int
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
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
     * 续费开始时间
     * @return int
     */
    public function getRenewTime()
    {
        return $this->renewTime;
    }

    /**
     * 续费付款时间
     * @return int
     */
    public function getRenewPayTime()
    {
        return $this->renewPayTime;
    }

    /**
     * 交易总额
     * @return mixed
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * 交易额度阈值
     * @return mixed
     */
    public function getOrderThreshold()
    {
        return $this->orderThreshold;
    }

}
