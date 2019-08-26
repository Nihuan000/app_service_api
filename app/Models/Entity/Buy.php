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
 * 求购信息表
 * @Entity()
 * @Table(name="sb_buy")
 * @uses      Buy
 */
class Buy extends Model
{
    /**
     * @var int $buyId 
     * @Id()
     * @Column(name="buy_id", type="integer")
     */
    private $buyId;

    /**
     * @var int $userId 用户ID
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $oldUserId 合并数据原归属ID
     * @Column(name="old_user_id", type="integer", default=0)
     */
    private $oldUserId;

    /**
     * @var string $title 求购标题
     * @Column(name="title", type="string", length=100, default="")
     */
    private $title;

    /**
     * @var string $pic 图片
     * @Column(name="pic", type="string", length=100, default="")
     */
    private $pic;

    /**
     * @var int $amount 数量
     * @Column(name="amount", type="integer", default=0)
     */
    private $amount;

    /**
     * @var string $unit 单位
     * @Column(name="unit", type="string", length=30, default="")
     */
    private $unit;

    /**
     * @var int $reward 有偿找样
     * @Column(name="reward", type="integer", default=0)
     */
    private $reward;

    /**
     * @var int $isCustomize 是否接受定做 0：否 1：是
     * @Column(name="is_customize", type="tinyint", default=0)
     */
    private $isCustomize;

    /**
     * @var string $remark 需求说明
     * @Column(name="remark", type="string", length=255, default="")
     */
    private $remark;

    /**
     * @var string $contacts 联系人
     * @Column(name="contacts", type="string", length=45, default="")
     */
    private $contacts;

    /**
     * @var string $contactNum 联系电话
     * @Column(name="contact_num", type="string", length=20, default="")
     */
    private $contactNum;

    /**
     * @var int $isAudit 审核 :0通过 ,1 审核中, 2审核失败 3 暂不审核 4 锁定 5暂时通过
     * @Column(name="is_audit", type="tinyint", default=0)
     */
    private $isAudit;

    /**
     * @var int $auditTime 审核时间
     * @Column(name="audit_time", type="integer", default=0)
     */
    private $auditTime;

    /**
     * @var int $auditId 审核人id
     * @Column(name="audit_id", type="integer", default=0)
     */
    private $auditId;

    /**
     * @var string $cause 审核失败原因
     * @Column(name="cause", type="string", length=200, default="")
     */
    private $cause;

    /**
     * @var int $type 采购类型：1面料，2辅料，3加工服务
     * @Column(name="type", type="tinyint", default=1)
     */
    private $type;

    /**
     * @var string $voice 语音文件
     * @Column(name="voice", type="string", length=100, default="")
     */
    private $voice;

    /**
     * @var int $voiceTime 语音时间秒计算
     * @Column(name="voice_time", type="tinyint", default=0)
     */
    private $voiceTime;

    /**
     * @var int $status 找布状态 0:未结束 1:已结束 2:已过期
     * @Column(name="status", type="tinyint", default=0)
     */
    private $status;

    /**
     * @var int $delStatus 删除状态 1正常 2删除
     * @Column(name="del_status", type="tinyint", default=1)
     */
    private $delStatus;

    /**
     * @var string $pushKey 推送关键字
     * @Column(name="push_key", type="string", length=100, default="")
     */
    private $pushKey;

    /**
     * @var int $pushStatus 1未推送 2推送中 3已推送 4推送失败
     * @Column(name="push_status", type="integer", default=1)
     */
    private $pushStatus;

    /**
     * @var int $phoneIsPublic 联系方式是否公开 0不公开 1公开
     * @Column(name="phone_is_public", type="tinyint", default=1)
     */
    private $phoneIsPublic;

    /**
     * @var int $clicks 点击量
     * @Column(name="clicks", type="integer", default=0)
     */
    private $clicks;

    /**
     * @var int $fromType 发布来源  0 APP1安卓 2IOS 9 T100 10微信
     * @Column(name="from_type", type="integer")
     */
    private $fromType;

    /**
     * @var int $addTime 提交时间
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

    /**
     * @var int $alterTime 修改时间
     * @Column(name="alter_time", type="integer", default=0)
     */
    private $alterTime;

    /**
     * @var int $refreshTime 刷新时间
     * @Column(name="refresh_time", type="integer", default=0)
     */
    private $refreshTime;

    /**
     * @var int $expireTime 失效时间
     * @Column(name="expire_time", type="integer", default=0)
     */
    private $expireTime;

    /**
     * @var int $refreshCount 刷新次数
     * @Column(name="refresh_count", type="integer", default=0)
     */
    private $refreshCount;

    /**
     * @var int $smsStatus 短信发送状态 1未发送 2发送中 3已发送
     * @Column(name="sms_status", type="tinyint", default=1)
     */
    private $smsStatus;

    /**
     * @var int $toVip 推送对象 1全部 2vip
     * @Column(name="to_vip", type="tinyint", default=1)
     */
    private $toVip;

    /**
     * @var int $isFind 采购状态 0 未找到过 1 已找到过
     * @Column(name="is_find", type="tinyint", default=0)
     */
    private $isFind;

    /**
     * @var int $findType 结束找布类型 1:搜布已找到 2:线下已找到 3:没有符合需求的报价 4:不找了
     * @Column(name="find_type", type="smallint", default=0)
     */
    private $findType;

    /**
     * @var float $gramW 克重
     * @Column(name="gram_w", type="double", default=0)
     */
    private $gramW;

    /**
     * @var float $pWidth 幅宽
     * @Column(name="p_width", type="double", default=0)
     */
    private $pWidth;

    /**
     * @var float $minPrice 价格区间最小值
     * @Column(name="min_price", type="double", default=0)
     */
    private $minPrice;

    /**
     * @var float $maxPrice 价格区间最大值
     * @Column(name="max_price", type="double")
     * @Required()
     */
    private $maxPrice;

    /**
     * @var string $frontLabelDesc 前台标签描述
     * @Column(name="front_label_desc", type="string", length=500)
     * @Required()
     */
    private $frontLabelDesc;

    /**
     * @var string $frontLabel 前台标签
     * @Column(name="front_label", type="string", length=255)
     * @Required()
     */
    private $frontLabel;

    /**
     * @var int $offerUnreadCount 未读报价数
     * @Column(name="offer_unread_count", type="integer", default=0)
     */
    private $offerUnreadCount;

    /**
     * @var int $fabricType 采购分类：1针织2梭织3其他
     * @Column(name="fabric_type", type="tinyint", default=0)
     */
    private $fabricType;

    /**
     * @var int $isNoticed 0:未通知1：已通知
     * @Column(name="is_noticed", type="tinyint", default=0)
     */
    private $isNoticed;

    /**
     * @var int $buyFixed 一口价采购 0：否 1：是
     * @Column(name="buy_fixed", type="tinyint", default=0)
     */
    private $buyFixed;

    /**
     * @var float $fixedAmount 一口价金额(总价+运费)
     * @Column(name="fixed_amount", type="decimal", default=0)
     */
    private $fixedAmount;

    /**
     * @var string $standard 规格
     * @Column(name="standard", type="string", length=255)
     * @Required()
     */
    private $standard;

    /**
     * @var int $thickness 厚度
     * @Column(name="thickness", type="integer")
     * @Required()
     */
    private $thickness;

    /**
     * @var int $onlineFindType 线上找到类型 1:报价找到 2:搜索产品找到
     * @Column(name="online_find_type", type="smallint", default=0)
     */
    private $onlineFindType;

    /**
     * @var string $matchingOfferId 符合报价的offer_id,用逗号分隔
     * @Column(name="matching_offer_id", type="string", length=255, default="")
     */
    private $matchingOfferId;

    /**
     * @var string $notFindReason 未找到原因 1:风格不匹配 2:价格偏高 3:没有现货 4:供应商回复太慢了 5:其他 6:线下先找到了
     * @Column(name="not_find_reason", type="string", length=255, default="")
     */
    private $notFindReason;

    /**
     * @var int $earnestId 诚意金记录表主键id
     * @Column(name="earnest_id", type="integer", default=0)
     */
    private $earnestId;

    /**
     * @var int $overTime 结束找布时间
     * @Column(name="over_time", type="integer", default=0)
     */
    private $overTime;

    /**
     * @param int $value
     * @return $this
     */
    public function setBuyId(int $value)
    {
        $this->buyId = $value;

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
     * 合并数据原归属ID
     * @param int $value
     * @return $this
     */
    public function setOldUserId(int $value): self
    {
        $this->oldUserId = $value;

        return $this;
    }

    /**
     * 求购标题
     * @param string $value
     * @return $this
     */
    public function setTitle(string $value): self
    {
        $this->title = $value;

        return $this;
    }

    /**
     * 图片
     * @param string $value
     * @return $this
     */
    public function setPic(string $value): self
    {
        $this->pic = $value;

        return $this;
    }

    /**
     * 数量
     * @param int $value
     * @return $this
     */
    public function setAmount(int $value): self
    {
        $this->amount = $value;

        return $this;
    }

    /**
     * 单位
     * @param string $value
     * @return $this
     */
    public function setUnit(string $value): self
    {
        $this->unit = $value;

        return $this;
    }

    /**
     * 有偿找样
     * @param int $value
     * @return $this
     */
    public function setReward(int $value): self
    {
        $this->reward = $value;

        return $this;
    }

    /**
     * 是否接受定做 0：否 1：是
     * @param int $value
     * @return $this
     */
    public function setIsCustomize(int $value): self
    {
        $this->isCustomize = $value;

        return $this;
    }

    /**
     * 需求说明
     * @param string $value
     * @return $this
     */
    public function setRemark(string $value): self
    {
        $this->remark = $value;

        return $this;
    }

    /**
     * 联系人
     * @param string $value
     * @return $this
     */
    public function setContacts(string $value): self
    {
        $this->contacts = $value;

        return $this;
    }

    /**
     * 联系电话
     * @param string $value
     * @return $this
     */
    public function setContactNum(string $value): self
    {
        $this->contactNum = $value;

        return $this;
    }

    /**
     * 审核 :0通过 ,1 审核中, 2审核失败 3 暂不审核 4 锁定 5暂时通过
     * @param int $value
     * @return $this
     */
    public function setIsAudit(int $value): self
    {
        $this->isAudit = $value;

        return $this;
    }

    /**
     * 审核时间
     * @param int $value
     * @return $this
     */
    public function setAuditTime(int $value): self
    {
        $this->auditTime = $value;

        return $this;
    }

    /**
     * 审核人id
     * @param int $value
     * @return $this
     */
    public function setAuditId(int $value): self
    {
        $this->auditId = $value;

        return $this;
    }

    /**
     * 审核失败原因
     * @param string $value
     * @return $this
     */
    public function setCause(string $value): self
    {
        $this->cause = $value;

        return $this;
    }

    /**
     * 采购类型：1面料，2辅料，3加工服务
     * @param int $value
     * @return $this
     */
    public function setType(int $value): self
    {
        $this->type = $value;

        return $this;
    }

    /**
     * 语音文件
     * @param string $value
     * @return $this
     */
    public function setVoice(string $value): self
    {
        $this->voice = $value;

        return $this;
    }

    /**
     * 语音时间秒计算
     * @param int $value
     * @return $this
     */
    public function setVoiceTime(int $value): self
    {
        $this->voiceTime = $value;

        return $this;
    }

    /**
     * 找布状态 0:未结束 1:已结束 2:已过期
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * 删除状态 1正常 2删除
     * @param int $value
     * @return $this
     */
    public function setDelStatus(int $value): self
    {
        $this->delStatus = $value;

        return $this;
    }

    /**
     * 推送关键字
     * @param string $value
     * @return $this
     */
    public function setPushKey(string $value): self
    {
        $this->pushKey = $value;

        return $this;
    }

    /**
     * 1未推送 2推送中 3已推送 4推送失败
     * @param int $value
     * @return $this
     */
    public function setPushStatus(int $value): self
    {
        $this->pushStatus = $value;

        return $this;
    }

    /**
     * 联系方式是否公开 0不公开 1公开
     * @param int $value
     * @return $this
     */
    public function setPhoneIsPublic(int $value): self
    {
        $this->phoneIsPublic = $value;

        return $this;
    }

    /**
     * 点击量
     * @param int $value
     * @return $this
     */
    public function setClicks(int $value): self
    {
        $this->clicks = $value;

        return $this;
    }

    /**
     * 发布来源  0 APP1安卓 2IOS 9 T100 10微信
     * @param int $value
     * @return $this
     */
    public function setFromType(int $value): self
    {
        $this->fromType = $value;

        return $this;
    }

    /**
     * 提交时间
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
    public function setAlterTime(int $value): self
    {
        $this->alterTime = $value;

        return $this;
    }

    /**
     * 刷新时间
     * @param int $value
     * @return $this
     */
    public function setRefreshTime(int $value): self
    {
        $this->refreshTime = $value;

        return $this;
    }

    /**
     * 失效时间
     * @param int $value
     * @return $this
     */
    public function setExpireTime(int $value): self
    {
        $this->expireTime = $value;

        return $this;
    }

    /**
     * 刷新次数
     * @param int $value
     * @return $this
     */
    public function setRefreshCount(int $value): self
    {
        $this->refreshCount = $value;

        return $this;
    }

    /**
     * 短信发送状态 1未发送 2发送中 3已发送
     * @param int $value
     * @return $this
     */
    public function setSmsStatus(int $value): self
    {
        $this->smsStatus = $value;

        return $this;
    }

    /**
     * 推送对象 1全部 2vip
     * @param int $value
     * @return $this
     */
    public function setToVip(int $value): self
    {
        $this->toVip = $value;

        return $this;
    }

    /**
     * 采购状态 0 未找到过 1 已找到过
     * @param int $value
     * @return $this
     */
    public function setIsFind(int $value): self
    {
        $this->isFind = $value;

        return $this;
    }

    /**
     * 结束找布类型 1:搜布已找到 2:线下已找到 3:没有符合需求的报价 4:不找了
     * @param int $value
     * @return $this
     */
    public function setFindType(int $value): self
    {
        $this->findType = $value;

        return $this;
    }

    /**
     * 克重
     * @param float $value
     * @return $this
     */
    public function setGramW(float $value): self
    {
        $this->gramW = $value;

        return $this;
    }

    /**
     * 幅宽
     * @param float $value
     * @return $this
     */
    public function setPWidth(float $value): self
    {
        $this->pWidth = $value;

        return $this;
    }

    /**
     * 价格区间最小值
     * @param float $value
     * @return $this
     */
    public function setMinPrice(float $value): self
    {
        $this->minPrice = $value;

        return $this;
    }

    /**
     * 价格区间最大值
     * @param float $value
     * @return $this
     */
    public function setMaxPrice(float $value): self
    {
        $this->maxPrice = $value;

        return $this;
    }

    /**
     * 前台标签描述
     * @param string $value
     * @return $this
     */
    public function setFrontLabelDesc(string $value): self
    {
        $this->frontLabelDesc = $value;

        return $this;
    }

    /**
     * 前台标签
     * @param string $value
     * @return $this
     */
    public function setFrontLabel(string $value): self
    {
        $this->frontLabel = $value;

        return $this;
    }

    /**
     * 未读报价数
     * @param int $value
     * @return $this
     */
    public function setOfferUnreadCount(int $value): self
    {
        $this->offerUnreadCount = $value;

        return $this;
    }

    /**
     * 采购分类：1针织2梭织3其他
     * @param int $value
     * @return $this
     */
    public function setFabricType(int $value): self
    {
        $this->fabricType = $value;

        return $this;
    }

    /**
     * 0:未通知1：已通知
     * @param int $value
     * @return $this
     */
    public function setIsNoticed(int $value): self
    {
        $this->isNoticed = $value;

        return $this;
    }

    /**
     * 一口价采购 0：否 1：是
     * @param int $value
     * @return $this
     */
    public function setBuyFixed(int $value): self
    {
        $this->buyFixed = $value;

        return $this;
    }

    /**
     * 一口价金额(总价+运费)
     * @param float $value
     * @return $this
     */
    public function setFixedAmount(float $value): self
    {
        $this->fixedAmount = $value;

        return $this;
    }

    /**
     * 规格
     * @param string $value
     * @return $this
     */
    public function setStandard(string $value): self
    {
        $this->standard = $value;

        return $this;
    }

    /**
     * 厚度
     * @param int $value
     * @return $this
     */
    public function setThickness(int $value): self
    {
        $this->thickness = $value;

        return $this;
    }

    /**
     * 线上找到类型 1:报价找到 2:搜索产品找到
     * @param int $value
     * @return $this
     */
    public function setOnlineFindType(int $value): self
    {
        $this->onlineFindType = $value;

        return $this;
    }

    /**
     * 符合报价的offer_id,用逗号分隔
     * @param string $value
     * @return $this
     */
    public function setMatchingOfferId(string $value): self
    {
        $this->matchingOfferId = $value;

        return $this;
    }

    /**
     * 未找到原因 1:风格不匹配 2:价格偏高 3:没有现货 4:供应商回复太慢了 5:其他 6:线下先找到了
     * @param string $value
     * @return $this
     */
    public function setNotFindReason(string $value): self
    {
        $this->notFindReason = $value;

        return $this;
    }

    /**
     * 诚意金记录表主键id
     * @param int $value
     * @return $this
     */
    public function setEarnestId(int $value): self
    {
        $this->earnestId = $value;

        return $this;
    }

    /**
     * 结束找布时间
     * @param int $value
     * @return $this
     */
    public function setOverTime(int $value): self
    {
        $this->overTime = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBuyId()
    {
        return $this->buyId;
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
     * 合并数据原归属ID
     * @return int
     */
    public function getOldUserId()
    {
        return $this->oldUserId;
    }

    /**
     * 求购标题
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * 图片
     * @return string
     */
    public function getPic()
    {
        return $this->pic;
    }

    /**
     * 数量
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * 单位
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * 有偿找样
     * @return int
     */
    public function getReward()
    {
        return $this->reward;
    }

    /**
     * 是否接受定做 0：否 1：是
     * @return int
     */
    public function getIsCustomize()
    {
        return $this->isCustomize;
    }

    /**
     * 需求说明
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * 联系人
     * @return string
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * 联系电话
     * @return string
     */
    public function getContactNum()
    {
        return $this->contactNum;
    }

    /**
     * 审核 :0通过 ,1 审核中, 2审核失败 3 暂不审核 4 锁定 5暂时通过
     * @return int
     */
    public function getIsAudit()
    {
        return $this->isAudit;
    }

    /**
     * 审核时间
     * @return int
     */
    public function getAuditTime()
    {
        return $this->auditTime;
    }

    /**
     * 审核人id
     * @return int
     */
    public function getAuditId()
    {
        return $this->auditId;
    }

    /**
     * 审核失败原因
     * @return string
     */
    public function getCause()
    {
        return $this->cause;
    }

    /**
     * 采购类型：1面料，2辅料，3加工服务
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 语音文件
     * @return string
     */
    public function getVoice()
    {
        return $this->voice;
    }

    /**
     * 语音时间秒计算
     * @return int
     */
    public function getVoiceTime()
    {
        return $this->voiceTime;
    }

    /**
     * 找布状态 0:未结束 1:已结束 2:已过期
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 删除状态 1正常 2删除
     * @return mixed
     */
    public function getDelStatus()
    {
        return $this->delStatus;
    }

    /**
     * 推送关键字
     * @return string
     */
    public function getPushKey()
    {
        return $this->pushKey;
    }

    /**
     * 1未推送 2推送中 3已推送 4推送失败
     * @return mixed
     */
    public function getPushStatus()
    {
        return $this->pushStatus;
    }

    /**
     * 联系方式是否公开 0不公开 1公开
     * @return mixed
     */
    public function getPhoneIsPublic()
    {
        return $this->phoneIsPublic;
    }

    /**
     * 点击量
     * @return int
     */
    public function getClicks()
    {
        return $this->clicks;
    }

    /**
     * 发布来源  0 APP1安卓 2IOS 9 T100 10微信
     * @return int
     */
    public function getFromType()
    {
        return $this->fromType;
    }

    /**
     * 提交时间
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
    public function getAlterTime()
    {
        return $this->alterTime;
    }

    /**
     * 刷新时间
     * @return int
     */
    public function getRefreshTime()
    {
        return $this->refreshTime;
    }

    /**
     * 失效时间
     * @return int
     */
    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * 刷新次数
     * @return int
     */
    public function getRefreshCount()
    {
        return $this->refreshCount;
    }

    /**
     * 短信发送状态 1未发送 2发送中 3已发送
     * @return mixed
     */
    public function getSmsStatus()
    {
        return $this->smsStatus;
    }

    /**
     * 推送对象 1全部 2vip
     * @return mixed
     */
    public function getToVip()
    {
        return $this->toVip;
    }

    /**
     * 采购状态 0 未找到过 1 已找到过
     * @return int
     */
    public function getIsFind()
    {
        return $this->isFind;
    }

    /**
     * 结束找布类型 1:搜布已找到 2:线下已找到 3:没有符合需求的报价 4:不找了
     * @return int
     */
    public function getFindType()
    {
        return $this->findType;
    }

    /**
     * 克重
     * @return float
     */
    public function getGramW()
    {
        return $this->gramW;
    }

    /**
     * 幅宽
     * @return float
     */
    public function getPWidth()
    {
        return $this->pWidth;
    }

    /**
     * 价格区间最小值
     * @return float
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * 价格区间最大值
     * @return float
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }

    /**
     * 前台标签描述
     * @return string
     */
    public function getFrontLabelDesc()
    {
        return $this->frontLabelDesc;
    }

    /**
     * 前台标签
     * @return string
     */
    public function getFrontLabel()
    {
        return $this->frontLabel;
    }

    /**
     * 未读报价数
     * @return int
     */
    public function getOfferUnreadCount()
    {
        return $this->offerUnreadCount;
    }

    /**
     * 采购分类：1针织2梭织3其他
     * @return int
     */
    public function getFabricType()
    {
        return $this->fabricType;
    }

    /**
     * 0:未通知1：已通知
     * @return int
     */
    public function getIsNoticed()
    {
        return $this->isNoticed;
    }

    /**
     * 一口价采购 0：否 1：是
     * @return int
     */
    public function getBuyFixed()
    {
        return $this->buyFixed;
    }

    /**
     * 一口价金额(总价+运费)
     * @return mixed
     */
    public function getFixedAmount()
    {
        return $this->fixedAmount;
    }

    /**
     * 规格
     * @return string
     */
    public function getStandard()
    {
        return $this->standard;
    }

    /**
     * 厚度
     * @return int
     */
    public function getThickness()
    {
        return $this->thickness;
    }

    /**
     * 线上找到类型 1:报价找到 2:搜索产品找到
     * @return int
     */
    public function getOnlineFindType()
    {
        return $this->onlineFindType;
    }

    /**
     * 符合报价的offer_id,用逗号分隔
     * @return string
     */
    public function getMatchingOfferId()
    {
        return $this->matchingOfferId;
    }

    /**
     * 未找到原因 1:风格不匹配 2:价格偏高 3:没有现货 4:供应商回复太慢了 5:其他 6:线下先找到了
     * @return string
     */
    public function getNotFindReason()
    {
        return $this->notFindReason;
    }

    /**
     * 诚意金记录表主键id
     * @return int
     */
    public function getEarnestId()
    {
        return $this->earnestId;
    }

    /**
     * 结束找布时间
     * @return int
     */
    public function getOverTime()
    {
        return $this->overTime;
    }

}
