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
 * 活跃分记录表(目前只有产品审核成功及订单完成)
 * @Entity()
 * @Table(name="sb_user_score_get_record")
 * @uses      UserScoreGetRecord
 */
class UserScoreGetRecord extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $optUserId 操作人id: 用户支持指定动作扣分（后台发起）,0:脚本
     * @Column(name="opt_user_id", type="integer", default=0)
     */
    private $optUserId;

    /**
     * @var int $optUserType 操作人类型 1:前台 2:后台 3:脚本
     * @Column(name="opt_user_type", type="integer", default=0)
     */
    private $optUserType;

    /**
     * @var int $getRuleId 获取积分的规则id sb_user_score_get_rule的主键id
     * @Column(name="get_rule_id", type="integer", default=0)
     */
    private $getRuleId;

    /**
     * @var int $scoreValue 积分值
     * @Column(name="score_value", type="integer", default=0)
     */
    private $scoreValue;

    /**
     * @var int $oldScore 旧活跃积分值,冗余字段,脚本跑的时候为0
     * @Column(name="old_score", type="integer", default=0)
     */
    private $oldScore;

    /**
     * @var int $newScore 新活跃积分值,冗余字段,脚本跑的时候为0
     * @Column(name="new_score", type="integer", default=0)
     */
    private $newScore;

    /**
     * @var string $title 标题
     * @Column(name="title", type="string", length=50, default="")
     */
    private $title;

    /**
     * @var string $desc 描述
     * @Column(name="desc", type="string", length=100, default="")
     */
    private $desc;

    /**
     * @var int $addTime 积分获取时间
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

    /**
     * @var string $orderNum 订单号,冗余字段
     * @Column(name="order_num", type="string", length=32, default="")
     */
    private $orderNum;

    /**
     * @var int $productId 产品id,冗余字段
     * @Column(name="product_id", type="integer", default=0)
     */
    private $productId;

    /**
     * @var int $isValid 0:无效 1:有效
     * @Column(name="is_valid", type="tinyint", default=1)
     */
    private $isValid;

    /**
     * @var int $unValidType 1:黑名单卖家 2:收货时间-下单时间<1天 3:买家设备被使用达到上限 4:非有效订单(order_remark is_valid不为1)
     * @Column(name="un_valid_type", type="smallint", default=0)
     */
    private $unValidType;

    /**
     * @var int $expireTime 过期时间
     * @Column(name="expire_time", type="integer", default=0)
     */
    private $expireTime;

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
     * 操作人id: 用户支持指定动作扣分（后台发起）,0:脚本
     * @param int $value
     * @return $this
     */
    public function setOptUserId(int $value): self
    {
        $this->optUserId = $value;

        return $this;
    }

    /**
     * 操作人类型 1:前台 2:后台 3:脚本
     * @param int $value
     * @return $this
     */
    public function setOptUserType(int $value): self
    {
        $this->optUserType = $value;

        return $this;
    }

    /**
     * 获取积分的规则id sb_user_score_get_rule的主键id
     * @param int $value
     * @return $this
     */
    public function setGetRuleId(int $value): self
    {
        $this->getRuleId = $value;

        return $this;
    }

    /**
     * 积分值
     * @param int $value
     * @return $this
     */
    public function setScoreValue(int $value): self
    {
        $this->scoreValue = $value;

        return $this;
    }

    /**
     * 旧活跃积分值,冗余字段,脚本跑的时候为0
     * @param int $value
     * @return $this
     */
    public function setOldScore(int $value): self
    {
        $this->oldScore = $value;

        return $this;
    }

    /**
     * 新活跃积分值,冗余字段,脚本跑的时候为0
     * @param int $value
     * @return $this
     */
    public function setNewScore(int $value): self
    {
        $this->newScore = $value;

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
     * 描述
     * @param string $value
     * @return $this
     */
    public function setDesc(string $value): self
    {
        $this->desc = $value;

        return $this;
    }

    /**
     * 积分获取时间
     * @param int $value
     * @return $this
     */
    public function setAddTime(int $value): self
    {
        $this->addTime = $value;

        return $this;
    }

    /**
     * 订单号,冗余字段
     * @param string $value
     * @return $this
     */
    public function setOrderNum(string $value): self
    {
        $this->orderNum = $value;

        return $this;
    }

    /**
     * 产品id,冗余字段
     * @param int $value
     * @return $this
     */
    public function setProductId(int $value): self
    {
        $this->productId = $value;

        return $this;
    }

    /**
     * 0:无效 1:有效
     * @param int $value
     * @return $this
     */
    public function setIsValid(int $value): self
    {
        $this->isValid = $value;

        return $this;
    }

    /**
     * 1:黑名单卖家 2:收货时间-下单时间<1天 3:买家设备被使用达到上限 4:非有效订单(order_remark is_valid不为1)
     * @param int $value
     * @return $this
     */
    public function setUnValidType(int $value): self
    {
        $this->unValidType = $value;

        return $this;
    }

    /**
     * 过期时间
     * @param int $value
     * @return $this
     */
    public function setExpireTime(int $value): self
    {
        $this->expireTime = $value;

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
     * 操作人id: 用户支持指定动作扣分（后台发起）,0:脚本
     * @return int
     */
    public function getOptUserId()
    {
        return $this->optUserId;
    }

    /**
     * 操作人类型 1:前台 2:后台 3:脚本
     * @return int
     */
    public function getOptUserType()
    {
        return $this->optUserType;
    }

    /**
     * 获取积分的规则id sb_user_score_get_rule的主键id
     * @return int
     */
    public function getGetRuleId()
    {
        return $this->getRuleId;
    }

    /**
     * 积分值
     * @return int
     */
    public function getScoreValue()
    {
        return $this->scoreValue;
    }

    /**
     * 旧活跃积分值,冗余字段,脚本跑的时候为0
     * @return int
     */
    public function getOldScore()
    {
        return $this->oldScore;
    }

    /**
     * 新活跃积分值,冗余字段,脚本跑的时候为0
     * @return int
     */
    public function getNewScore()
    {
        return $this->newScore;
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
     * 描述
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * 积分获取时间
     * @return int
     */
    public function getAddTime()
    {
        return $this->addTime;
    }

    /**
     * 订单号,冗余字段
     * @return string
     */
    public function getOrderNum()
    {
        return $this->orderNum;
    }

    /**
     * 产品id,冗余字段
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * 0:无效 1:有效
     * @return mixed
     */
    public function getIsValid()
    {
        return $this->isValid;
    }

    /**
     * 1:黑名单卖家 2:收货时间-下单时间<1天 3:买家设备被使用达到上限 4:非有效订单(order_remark is_valid不为1)
     * @return int
     */
    public function getUnValidType()
    {
        return $this->unValidType;
    }

    /**
     * 过期时间
     * @return int
     */
    public function getExpireTime()
    {
        return $this->expireTime;
    }

}
