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
 * 不活跃用户激活短信发送表
 * @Entity()
 * @Table(name="sb_activate_sms")
 * @uses      ActivateSms
 */
class ActivateSms extends Model
{
    /**
     * @var int $acId 
     * @Id()
     * @Column(name="ac_id", type="integer")
     */
    private $acId;

    /**
     * @var string $phone 用户手机号
     * @Column(name="phone", type="string", length=15, default="")
     */
    private $phone;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var string $orderNum 订单号
     * @Column(name="order_num", type="string", length=32)
     * @Required()
     */
    private $orderNum;

    /**
     * @var int $msgType 1:报价未读 2:产品被收藏 3:报价已读 4:卖家24小时未发货 5:卖家36小时未发货 6:首单采购商提醒确认收货好评（订单发货96小时） 7. 未读留言提醒 8:非活跃召回
     * @Column(name="msg_type", type="tinyint", default=0)
     */
    private $msgType;

    /**
     * @var int $sendTime 发送时间
     * @Column(name="send_time", type="integer", default=0)
     */
    private $sendTime;

    /**
     * @var int $exprieTime 过期时间
     * @Column(name="exprie_time", type="integer", default=0)
     */
    private $exprieTime;

    /**
     * @var string $msgContent 短信内容
     * @Column(name="msg_content", type="string", length=150, default="")
     */
    private $msgContent;

    /**
     * @var int $sendStatus 0:未发送 1：已发送
     * @Column(name="send_status", type="tinyint", default=0)
     */
    private $sendStatus;

    /**
     * @param int $value
     * @return $this
     */
    public function setAcId(int $value)
    {
        $this->acId = $value;

        return $this;
    }

    /**
     * 用户手机号
     * @param string $value
     * @return $this
     */
    public function setPhone(string $value): self
    {
        $this->phone = $value;

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
     * 订单号
     * @param string $value
     * @return $this
     */
    public function setOrderNum(string $value): self
    {
        $this->orderNum = $value;

        return $this;
    }

    /**
     * 1:报价未读 2:产品被收藏 3:报价已读 4:卖家24小时未发货 5:卖家36小时未发货 6:首单采购商提醒确认收货好评（订单发货96小时） 7. 未读留言提醒
     * @param int $value
     * @return $this
     */
    public function setMsgType(int $value): self
    {
        $this->msgType = $value;

        return $this;
    }

    /**
     * 发送时间
     * @param int $value
     * @return $this
     */
    public function setSendTime(int $value): self
    {
        $this->sendTime = $value;

        return $this;
    }

    /**
     * 过期时间
     * @param int $value
     * @return $this
     */
    public function setExprieTime(int $value): self
    {
        $this->exprieTime = $value;

        return $this;
    }

    /**
     * 短信内容
     * @param string $value
     * @return $this
     */
    public function setMsgContent(string $value): self
    {
        $this->msgContent = $value;

        return $this;
    }

    /**
     * 0:未发送 1：已发送
     * @param int $value
     * @return $this
     */
    public function setSendStatus(int $value): self
    {
        $this->sendStatus = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAcId()
    {
        return $this->acId;
    }

    /**
     * 用户手机号
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
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
     * 订单号
     * @return string
     */
    public function getOrderNum()
    {
        return $this->orderNum;
    }

    /**
     * 1:报价未读 2:产品被收藏 3:报价已读 4:卖家24小时未发货 5:卖家36小时未发货 6:首单采购商提醒确认收货好评（订单发货96小时） 7. 未读留言提醒
     * @return int
     */
    public function getMsgType()
    {
        return $this->msgType;
    }

    /**
     * 发送时间
     * @return int
     */
    public function getSendTime()
    {
        return $this->sendTime;
    }

    /**
     * 过期时间
     * @return int
     */
    public function getExprieTime()
    {
        return $this->exprieTime;
    }

    /**
     * 短信内容
     * @return string
     */
    public function getMsgContent()
    {
        return $this->msgContent;
    }

    /**
     * 0:未发送 1：已发送
     * @return int
     */
    public function getSendStatus()
    {
        return $this->sendStatus;
    }

}
