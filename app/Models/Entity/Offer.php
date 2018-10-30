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
 * 报价表
 * @Entity()
 * @Table(name="sb_offer")
 * @uses      Offer
 */
class Offer extends Model
{
    /**
     * @var int $offerId 
     * @Id()
     * @Column(name="offer_id", type="integer")
     */
    private $offerId;

    /**
     * @var int $buyId 求购信息ID
     * @Column(name="buy_id", type="integer", default=0)
     */
    private $buyId;

    /**
     * @var int $userId 发布会员ID
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $oldUserId 合并数据原归属ID
     * @Column(name="old_user_id", type="integer", default=0)
     */
    private $oldUserId;

    /**
     * @var int $oldOffererId 合并数据原报价人ID
     * @Column(name="old_offerer_id", type="integer", default=0)
     */
    private $oldOffererId;

    /**
     * @var int $offererId 报价者ID
     * @Column(name="offerer_id", type="integer", default=0)
     */
    private $offererId;

    /**
     * @var string $offerPrice 报价
     * @Column(name="offer_price", type="string", length=50, default="''")
     */
    private $offerPrice;

    /**
     * @var string $units 大货单位
     * @Column(name="units", type="string", length=30, default="''")
     */
    private $units;

    /**
     * @var string $cutPrice 剪样报价
     * @Column(name="cut_price", type="string", length=50, default="''")
     */
    private $cutPrice;

    /**
     * @var string $cutUnits 剪样单位
     * @Column(name="cut_units", type="string", length=30, default="''")
     */
    private $cutUnits;

    /**
     * @var string $offerContent 报价留言
     * @Column(name="offer_content", type="string", length=200, default="''")
     */
    private $offerContent;

    /**
     * @var string $img 色卡图片
     * @Column(name="img", type="string", length=100, default="''")
     */
    private $img;

    /**
     * @var string $thumbImg 色卡缩略图
     * @Column(name="thumb_img", type="string", length=100, default="''")
     */
    private $thumbImg;

    /**
     * @var int $status 状态 0现货 1预定
     * @Column(name="status", type="tinyint", default=0)
     */
    private $status;

    /**
     * @var int $offerTime 报价时间
     * @Column(name="offer_time", type="integer", default=0)
     */
    private $offerTime;

    /**
     * @var int $isread 是否读取 0未读 1已读
     * @Column(name="isread", type="tinyint", default=0)
     */
    private $isread;

    /**
     * @var int $readTime 已读时间
     * @Column(name="read_time", type="integer")
     * @Required()
     */
    private $readTime;

    /**
     * @var int $isAudit 报价审核状态 0:未审核 1:已审核 2:审核失败
     * @Column(name="is_audit", type="tinyint", default=1)
     */
    private $isAudit;

    /**
     * @var int $userDel 发布者报价状态 1正常 0删除
     * @Column(name="user_del", type="tinyint", default=1)
     */
    private $userDel;

    /**
     * @var int $offererDel 报价人报价状态 1正常 0删除
     * @Column(name="offerer_del", type="tinyint", default=1)
     */
    private $offererDel;

    /**
     * @var int $offerQuality 报价准确读打分1-10分
     * @Column(name="offer_quality", type="tinyint", default=0)
     */
    private $offerQuality;

    /**
     * @var int $auditTime 审核时间
     * @Column(name="audit_time", type="integer", default=0)
     */
    private $auditTime;

    /**
     * @var int $auditId 审核者id
     * @Column(name="audit_id", type="integer", default=0)
     */
    private $auditId;

    /**
     * @var string $failCase 报价审核不通过原因
     * @Column(name="fail_case", type="string", length=100, default="''")
     */
    private $failCase;

    /**
     * @var int $isMatching 采纳状态，0:待采纳 1:已采纳 2:不采纳
     * @Column(name="is_matching", type="tinyint")
     * @Required()
     */
    private $isMatching;

    /**
     * @var int $matchingTime 匹配时间
     * @Column(name="matching_time", type="integer", default=0)
     */
    private $matchingTime;

    /**
     * @var int $matchingFrom 0:老数据 1:结束找布线上找到 2:采纳报价
     * @Column(name="matching_from", type="tinyint")
     * @Required()
     */
    private $matchingFrom;

    /**
     * @var int $offerSource 报价来源 1:首页 2:商机推荐 3:最新采购 4:综合排序采购 5:无报价采购 6:我的采购 7:他的采购 8:浏览记录 9:我的收藏 10:报价详情 11:同类采购推荐 12:外部 URL 13:通知 14:聊天 15:我的采购列表 16:聊天系统通知 17: 全部采购 18:发布产品并报价
     * @Column(name="offer_source", type="smallint")
     * @Required()
     */
    private $offerSource;

    /**
     * @var int $isDeleteInList 是否在列表中删除 0:否 1:是
     * @Column(name="is_delete_in_list", type="tinyint")
     * @Required()
     */
    private $isDeleteInList;

    /**
     * @var int $deleteInListTime 列表删除时间
     * @Column(name="delete_in_list_time", type="integer")
     * @Required()
     */
    private $deleteInListTime;

    /**
     * @var int $isConformByBuyer 买家标记报价是否符合 0:未标记 1:符合 2:不符合 99:<7.0.0版本的未标记
     * @Column(name="is_conform_by_buyer", type="tinyint")
     * @Required()
     */
    private $isConformByBuyer;

    /**
     * @var string $unConformReasonByBuyer 不符合报价的原因
     * @Column(name="un_conform_reason_by_buyer", type="string", length=255)
     * @Required()
     */
    private $unConformReasonByBuyer;

    /**
     * @var int $buyerConformTime 买家标记报价是否符合时间
     * @Column(name="buyer_conform_time", type="integer")
     * @Required()
     */
    private $buyerConformTime;

    /**
     * @var int $readPushStatus 0 未推送 1 不需要推送 2 已推送 
     * @Column(name="read_push_status", type="tinyint", default=0)
     */
    private $readPushStatus;

    /**
     * @param int $value
     * @return $this
     */
    public function setOfferId(int $value)
    {
        $this->offerId = $value;

        return $this;
    }

    /**
     * 求购信息ID
     * @param int $value
     * @return $this
     */
    public function setBuyId(int $value): self
    {
        $this->buyId = $value;

        return $this;
    }

    /**
     * 发布会员ID
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
     * 合并数据原报价人ID
     * @param int $value
     * @return $this
     */
    public function setOldOffererId(int $value): self
    {
        $this->oldOffererId = $value;

        return $this;
    }

    /**
     * 报价者ID
     * @param int $value
     * @return $this
     */
    public function setOffererId(int $value): self
    {
        $this->offererId = $value;

        return $this;
    }

    /**
     * 报价
     * @param string $value
     * @return $this
     */
    public function setOfferPrice(string $value): self
    {
        $this->offerPrice = $value;

        return $this;
    }

    /**
     * 大货单位
     * @param string $value
     * @return $this
     */
    public function setUnits(string $value): self
    {
        $this->units = $value;

        return $this;
    }

    /**
     * 剪样报价
     * @param string $value
     * @return $this
     */
    public function setCutPrice(string $value): self
    {
        $this->cutPrice = $value;

        return $this;
    }

    /**
     * 剪样单位
     * @param string $value
     * @return $this
     */
    public function setCutUnits(string $value): self
    {
        $this->cutUnits = $value;

        return $this;
    }

    /**
     * 报价留言
     * @param string $value
     * @return $this
     */
    public function setOfferContent(string $value): self
    {
        $this->offerContent = $value;

        return $this;
    }

    /**
     * 色卡图片
     * @param string $value
     * @return $this
     */
    public function setImg(string $value): self
    {
        $this->img = $value;

        return $this;
    }

    /**
     * 色卡缩略图
     * @param string $value
     * @return $this
     */
    public function setThumbImg(string $value): self
    {
        $this->thumbImg = $value;

        return $this;
    }

    /**
     * 状态 0现货 1预定
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * 报价时间
     * @param int $value
     * @return $this
     */
    public function setOfferTime(int $value): self
    {
        $this->offerTime = $value;

        return $this;
    }

    /**
     * 是否读取 0未读 1已读
     * @param int $value
     * @return $this
     */
    public function setIsread(int $value): self
    {
        $this->isread = $value;

        return $this;
    }

    /**
     * 已读时间
     * @param int $value
     * @return $this
     */
    public function setReadTime(int $value): self
    {
        $this->readTime = $value;

        return $this;
    }

    /**
     * 报价审核状态 0:未审核 1:已审核 2:审核失败
     * @param int $value
     * @return $this
     */
    public function setIsAudit(int $value): self
    {
        $this->isAudit = $value;

        return $this;
    }

    /**
     * 发布者报价状态 1正常 0删除
     * @param int $value
     * @return $this
     */
    public function setUserDel(int $value): self
    {
        $this->userDel = $value;

        return $this;
    }

    /**
     * 报价人报价状态 1正常 0删除
     * @param int $value
     * @return $this
     */
    public function setOffererDel(int $value): self
    {
        $this->offererDel = $value;

        return $this;
    }

    /**
     * 报价准确读打分1-10分
     * @param int $value
     * @return $this
     */
    public function setOfferQuality(int $value): self
    {
        $this->offerQuality = $value;

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
     * 审核者id
     * @param int $value
     * @return $this
     */
    public function setAuditId(int $value): self
    {
        $this->auditId = $value;

        return $this;
    }

    /**
     * 报价审核不通过原因
     * @param string $value
     * @return $this
     */
    public function setFailCase(string $value): self
    {
        $this->failCase = $value;

        return $this;
    }

    /**
     * 采纳状态，0:待采纳 1:已采纳 2:不采纳
     * @param int $value
     * @return $this
     */
    public function setIsMatching(int $value): self
    {
        $this->isMatching = $value;

        return $this;
    }

    /**
     * 匹配时间
     * @param int $value
     * @return $this
     */
    public function setMatchingTime(int $value): self
    {
        $this->matchingTime = $value;

        return $this;
    }

    /**
     * 0:老数据 1:结束找布线上找到 2:采纳报价
     * @param int $value
     * @return $this
     */
    public function setMatchingFrom(int $value): self
    {
        $this->matchingFrom = $value;

        return $this;
    }

    /**
     * 报价来源 1:首页 2:商机推荐 3:最新采购 4:综合排序采购 5:无报价采购 6:我的采购 7:他的采购 8:浏览记录 9:我的收藏 10:报价详情 11:同类采购推荐 12:外部 URL 13:通知 14:聊天 15:我的采购列表 16:聊天系统通知 17: 全部采购 18:发布产品并报价
     * @param int $value
     * @return $this
     */
    public function setOfferSource(int $value): self
    {
        $this->offerSource = $value;

        return $this;
    }

    /**
     * 是否在列表中删除 0:否 1:是
     * @param int $value
     * @return $this
     */
    public function setIsDeleteInList(int $value): self
    {
        $this->isDeleteInList = $value;

        return $this;
    }

    /**
     * 列表删除时间
     * @param int $value
     * @return $this
     */
    public function setDeleteInListTime(int $value): self
    {
        $this->deleteInListTime = $value;

        return $this;
    }

    /**
     * 买家标记报价是否符合 0:未标记 1:符合 2:不符合 99:<7.0.0版本的未标记
     * @param int $value
     * @return $this
     */
    public function setIsConformByBuyer(int $value): self
    {
        $this->isConformByBuyer = $value;

        return $this;
    }

    /**
     * 不符合报价的原因
     * @param string $value
     * @return $this
     */
    public function setUnConformReasonByBuyer(string $value): self
    {
        $this->unConformReasonByBuyer = $value;

        return $this;
    }

    /**
     * 买家标记报价是否符合时间
     * @param int $value
     * @return $this
     */
    public function setBuyerConformTime(int $value): self
    {
        $this->buyerConformTime = $value;

        return $this;
    }

    /**
     * 0 未推送 1 不需要推送 2 已推送 
     * @param int $value
     * @return $this
     */
    public function setReadPushStatus(int $value): self
    {
        $this->readPushStatus = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOfferId()
    {
        return $this->offerId;
    }

    /**
     * 求购信息ID
     * @return int
     */
    public function getBuyId()
    {
        return $this->buyId;
    }

    /**
     * 发布会员ID
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
     * 合并数据原报价人ID
     * @return int
     */
    public function getOldOffererId()
    {
        return $this->oldOffererId;
    }

    /**
     * 报价者ID
     * @return int
     */
    public function getOffererId()
    {
        return $this->offererId;
    }

    /**
     * 报价
     * @return mixed
     */
    public function getOfferPrice()
    {
        return $this->offerPrice;
    }

    /**
     * 大货单位
     * @return mixed
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * 剪样报价
     * @return mixed
     */
    public function getCutPrice()
    {
        return $this->cutPrice;
    }

    /**
     * 剪样单位
     * @return mixed
     */
    public function getCutUnits()
    {
        return $this->cutUnits;
    }

    /**
     * 报价留言
     * @return mixed
     */
    public function getOfferContent()
    {
        return $this->offerContent;
    }

    /**
     * 色卡图片
     * @return mixed
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * 色卡缩略图
     * @return mixed
     */
    public function getThumbImg()
    {
        return $this->thumbImg;
    }

    /**
     * 状态 0现货 1预定
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 报价时间
     * @return int
     */
    public function getOfferTime()
    {
        return $this->offerTime;
    }

    /**
     * 是否读取 0未读 1已读
     * @return int
     */
    public function getIsread()
    {
        return $this->isread;
    }

    /**
     * 已读时间
     * @return int
     */
    public function getReadTime()
    {
        return $this->readTime;
    }

    /**
     * 报价审核状态 0:未审核 1:已审核 2:审核失败
     * @return mixed
     */
    public function getIsAudit()
    {
        return $this->isAudit;
    }

    /**
     * 发布者报价状态 1正常 0删除
     * @return mixed
     */
    public function getUserDel()
    {
        return $this->userDel;
    }

    /**
     * 报价人报价状态 1正常 0删除
     * @return mixed
     */
    public function getOffererDel()
    {
        return $this->offererDel;
    }

    /**
     * 报价准确读打分1-10分
     * @return int
     */
    public function getOfferQuality()
    {
        return $this->offerQuality;
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
     * 审核者id
     * @return int
     */
    public function getAuditId()
    {
        return $this->auditId;
    }

    /**
     * 报价审核不通过原因
     * @return mixed
     */
    public function getFailCase()
    {
        return $this->failCase;
    }

    /**
     * 采纳状态，0:待采纳 1:已采纳 2:不采纳
     * @return int
     */
    public function getIsMatching()
    {
        return $this->isMatching;
    }

    /**
     * 匹配时间
     * @return int
     */
    public function getMatchingTime()
    {
        return $this->matchingTime;
    }

    /**
     * 0:老数据 1:结束找布线上找到 2:采纳报价
     * @return int
     */
    public function getMatchingFrom()
    {
        return $this->matchingFrom;
    }

    /**
     * 报价来源 1:首页 2:商机推荐 3:最新采购 4:综合排序采购 5:无报价采购 6:我的采购 7:他的采购 8:浏览记录 9:我的收藏 10:报价详情 11:同类采购推荐 12:外部 URL 13:通知 14:聊天 15:我的采购列表 16:聊天系统通知 17: 全部采购 18:发布产品并报价
     * @return int
     */
    public function getOfferSource()
    {
        return $this->offerSource;
    }

    /**
     * 是否在列表中删除 0:否 1:是
     * @return int
     */
    public function getIsDeleteInList()
    {
        return $this->isDeleteInList;
    }

    /**
     * 列表删除时间
     * @return int
     */
    public function getDeleteInListTime()
    {
        return $this->deleteInListTime;
    }

    /**
     * 买家标记报价是否符合 0:未标记 1:符合 2:不符合 99:<7.0.0版本的未标记
     * @return int
     */
    public function getIsConformByBuyer()
    {
        return $this->isConformByBuyer;
    }

    /**
     * 不符合报价的原因
     * @return string
     */
    public function getUnConformReasonByBuyer()
    {
        return $this->unConformReasonByBuyer;
    }

    /**
     * 买家标记报价是否符合时间
     * @return int
     */
    public function getBuyerConformTime()
    {
        return $this->buyerConformTime;
    }

    /**
     * 0 未推送 1 不需要推送 2 已推送 
     * @return int
     */
    public function getReadPushStatus()
    {
        return $this->readPushStatus;
    }

}
