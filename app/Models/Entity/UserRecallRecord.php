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
 * 用户召回记录表
 * @Entity()
 * @Table(name="sb_user_recall_record")
 * @uses      UserRecallRecord
 */
class UserRecallRecord extends Model
{
    /**
     * @var int $urrId id
     * @Id()
     * @Column(name="urr_id", type="integer")
     */
    private $urrId;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $regTime 注册时间
     * @Column(name="reg_time", type="integer", default=0)
     */
    private $regTime;

    /**
     * @var int $role 角色
     * @Column(name="role", type="tinyint", default=0)
     */
    private $role;

    /**
     * @var int $sendRole 发送类型采购商->1:无任何行为 2:有浏览产品 3:有搜索 4：有发布采购无报价 5：有发布有报价且有未读报价 6:有发布采购且有过已读报价
     * @Column(name="send_role", type="tinyint", default=0)
     */
    private $sendRole;

    /**
     * @var string $userNoticeLabel 关键词标签
     * @Column(name="user_notice_label", type="string", length=55, default="")
     */
    private $userNoticeLabel;

    /**
     * @var int $matchNum 匹配个数
     * @Column(name="match_num", type="integer", default=0)
     */
    private $matchNum;

    /**
     * @var int $msgType 消息类型 1：系统消息 2：个推通知
     * @Column(name="msg_type", type="tinyint", default=0)
     */
    private $msgType;

    /**
     * @var int $sendMsgTime 系统通知时间
     * @Column(name="send_msg_time", type="integer", default=0)
     */
    private $sendMsgTime;

    /**
     * @var int $msgIsReturn 消息通知后是否回归
     * @Column(name="msg_is_return", type="tinyint", default=0)
     */
    private $msgIsReturn;

    /**
     * @var int $sendSmsTime 短信通知时间
     * @Column(name="send_sms_time", type="integer", default=0)
     */
    private $sendSmsTime;

    /**
     * @var int $smsIsReturn 短信通知后是否回归
     * @Column(name="sms_is_return", type="tinyint", default=0)
     */
    private $smsIsReturn;

    /**
     * @var int $isDone 是否已完成 0:否 1：是
     * @Column(name="is_done", type="tinyint", default=0)
     */
    private $isDone;

    /**
     * @var int $addTime 记录时间
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

    /**
     * @var int $updateTime 修改时间
     * @Column(name="update_time", type="integer", default=0)
     */
    private $updateTime;

    /**
     * @var int $expireTime 回归记录判断有效期
     * @Column(name="expire_time", type="integer", default=0)
     */
    private $expireTime;

    /**
     * id
     * @param int $value
     * @return $this
     */
    public function setUrrId(int $value)
    {
        $this->urrId = $value;

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
     * 注册时间
     * @param int $value
     * @return $this
     */
    public function setRegTime(int $value): self
    {
        $this->regTime = $value;

        return $this;
    }

    /**
     * 角色
     * @param int $value
     * @return $this
     */
    public function setRole(int $value): self
    {
        $this->role = $value;

        return $this;
    }

    /**
     * 发送类型采购商->1:无任何行为 2:有浏览产品 3:有搜索 4：有发布采购无报价 5：有发布有报价且有未读报价 6:有发布采购且有过已读报价
     * @param int $value
     * @return $this
     */
    public function setSendRole(int $value): self
    {
        $this->sendRole = $value;

        return $this;
    }

    /**
     * 关键词标签
     * @param string $value
     * @return $this
     */
    public function setUserNoticeLabel(string $value): self
    {
        $this->userNoticeLabel = $value;

        return $this;
    }

    /**
     * 匹配个数
     * @param int $value
     * @return $this
     */
    public function setMatchNum(int $value): self
    {
        $this->matchNum = $value;

        return $this;
    }

    /**
     * 消息类型 1：系统消息 2：个推通知
     * @param int $value
     * @return $this
     */
    public function setMsgType(int $value): self
    {
        $this->msgType = $value;

        return $this;
    }

    /**
     * 系统通知时间
     * @param int $value
     * @return $this
     */
    public function setSendMsgTime(int $value): self
    {
        $this->sendMsgTime = $value;

        return $this;
    }

    /**
     * 消息通知后是否回归
     * @param int $value
     * @return $this
     */
    public function setMsgIsReturn(int $value): self
    {
        $this->msgIsReturn = $value;

        return $this;
    }

    /**
     * 短信通知时间
     * @param int $value
     * @return $this
     */
    public function setSendSmsTime(int $value): self
    {
        $this->sendSmsTime = $value;

        return $this;
    }

    /**
     * 短信通知后是否回归
     * @param int $value
     * @return $this
     */
    public function setSmsIsReturn(int $value): self
    {
        $this->smsIsReturn = $value;

        return $this;
    }

    /**
     * 是否已完成 0:否 1：是
     * @param int $value
     * @return $this
     */
    public function setIsDone(int $value): self
    {
        $this->isDone = $value;

        return $this;
    }

    /**
     * 记录时间
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
     * 回归记录判断有效期
     * @param int $value
     * @return $this
     */
    public function setExpireTime(int $value): self
    {
        $this->expireTime = $value;

        return $this;
    }

    /**
     * id
     * @return mixed
     */
    public function getUrrId()
    {
        return $this->urrId;
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
     * 注册时间
     * @return int
     */
    public function getRegTime()
    {
        return $this->regTime;
    }

    /**
     * 角色
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * 发送类型采购商->1:无任何行为 2:有浏览产品 3:有搜索 4：有发布采购无报价 5：有发布有报价且有未读报价 6:有发布采购且有过已读报价
     * @return int
     */
    public function getSendRole()
    {
        return $this->sendRole;
    }

    /**
     * 关键词标签
     * @return string
     */
    public function getUserNoticeLabel()
    {
        return $this->userNoticeLabel;
    }

    /**
     * 匹配个数
     * @return int
     */
    public function getMatchNum()
    {
        return $this->matchNum;
    }

    /**
     * 消息类型 1：系统消息 2：个推通知
     * @return int
     */
    public function getMsgType()
    {
        return $this->msgType;
    }

    /**
     * 系统通知时间
     * @return int
     */
    public function getSendMsgTime()
    {
        return $this->sendMsgTime;
    }

    /**
     * 消息通知后是否回归
     * @return int
     */
    public function getMsgIsReturn()
    {
        return $this->msgIsReturn;
    }

    /**
     * 短信通知时间
     * @return int
     */
    public function getSendSmsTime()
    {
        return $this->sendSmsTime;
    }

    /**
     * 短信通知后是否回归
     * @return int
     */
    public function getSmsIsReturn()
    {
        return $this->smsIsReturn;
    }

    /**
     * 是否已完成 0:否 1：是
     * @return int
     */
    public function getIsDone()
    {
        return $this->isDone;
    }

    /**
     * 记录时间
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
     * 回归记录判断有效期
     * @return int
     */
    public function getExpireTime()
    {
        return $this->expireTime;
    }

}
