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
 * 等级特权表

 * @Entity()
 * @Table(name="sb_user_level_authority")
 * @uses      UserLevelAuthority
 */
class UserLevelAuthority extends Model
{
    /**
     * @var int $auId 
     * @Column(name="au_id", type="integer")
     * @Required()
     */
    private $auId;

    /**
     * @var int $levelId 等级id
     * @Column(name="level_id", type="integer", default=0)
     */
    private $levelId;

    /**
     * @var int $hasBadge 权威徽章
     * @Column(name="has_badge", type="tinyint", default=1)
     */
    private $hasBadge;

    /**
     * @var int $businessLimit 商机推荐条数
     * @Column(name="business_limit", type="integer", default=0)
     */
    private $businessLimit;

    /**
     * @var int $takeContactDaily 主动联系采购
     * @Column(name="take_contact_daily", type="integer", default=0)
     */
    private $takeContactDaily;

    /**
     * @var int $productDesign 产品详情设计
     * @Column(name="product_design", type="integer", default=0)
     */
    private $productDesign;

    /**
     * @var int $productMarketing 产品推广报名
     * @Column(name="product_marketing", type="tinyint", default=0)
     */
    private $productMarketing;

    /**
     * @var int $eventRegister 活动报名特权
     * @Column(name="event_register", type="tinyint", default=0)
     */
    private $eventRegister;

    /**
     * @var int $shopRanking 店铺搜索排名
     * @Column(name="shop_ranking", type="tinyint", default=0)
     */
    private $shopRanking;

    /**
     * @var int $productRanking 产品搜索排名
     * @Column(name="product_ranking", type="tinyint", default=0)
     */
    private $productRanking;

    /**
     * @var int $addTime 添加时间
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

    /**
     * @var int $updateTime 修改时间
     * @Column(name="update_time", type="integer", default=0)
     */
    private $updateTime;

    /**
     * @var int $version 规则版本号
     * @Column(name="version", type="integer", default=0)
     */
    private $version;

    /**
     * @var int $status 权限使用状态:1正常0删除
     * @Column(name="status", type="tinyint", default=1)
     */
    private $status;

    /**
     * @var int $guaranteeTransaction 担保交易0关闭，1开启
     * @Column(name="guarantee_transaction", type="tinyint", default=0)
     */
    private $guaranteeTransaction;

    /**
     * @var int $buyPush 采购推送给供应商等级
     * @Column(name="buy_push", type="tinyint", default=0)
     */
    private $buyPush;

    /**
     * @var int $supplierPush 供应商推荐等级
     * @Column(name="Supplier_push", type="tinyint", default=0)
     */
    private $supplierPush;

    /**
     * @param int $value
     * @return $this
     */
    public function setAuId(int $value): self
    {
        $this->auId = $value;

        return $this;
    }

    /**
     * 等级id
     * @param int $value
     * @return $this
     */
    public function setLevelId(int $value): self
    {
        $this->levelId = $value;

        return $this;
    }

    /**
     * 权威徽章
     * @param int $value
     * @return $this
     */
    public function setHasBadge(int $value): self
    {
        $this->hasBadge = $value;

        return $this;
    }

    /**
     * 商机推荐条数
     * @param int $value
     * @return $this
     */
    public function setBusinessLimit(int $value): self
    {
        $this->businessLimit = $value;

        return $this;
    }

    /**
     * 主动联系采购
     * @param int $value
     * @return $this
     */
    public function setTakeContactDaily(int $value): self
    {
        $this->takeContactDaily = $value;

        return $this;
    }

    /**
     * 产品详情设计
     * @param int $value
     * @return $this
     */
    public function setProductDesign(int $value): self
    {
        $this->productDesign = $value;

        return $this;
    }

    /**
     * 产品推广报名
     * @param int $value
     * @return $this
     */
    public function setProductMarketing(int $value): self
    {
        $this->productMarketing = $value;

        return $this;
    }

    /**
     * 活动报名特权
     * @param int $value
     * @return $this
     */
    public function setEventRegister(int $value): self
    {
        $this->eventRegister = $value;

        return $this;
    }

    /**
     * 店铺搜索排名
     * @param int $value
     * @return $this
     */
    public function setShopRanking(int $value): self
    {
        $this->shopRanking = $value;

        return $this;
    }

    /**
     * 产品搜索排名
     * @param int $value
     * @return $this
     */
    public function setProductRanking(int $value): self
    {
        $this->productRanking = $value;

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
     * 权限使用状态:1正常0删除
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * 担保交易0关闭，1开启
     * @param int $value
     * @return $this
     */
    public function setGuaranteeTransaction(int $value): self
    {
        $this->guaranteeTransaction = $value;

        return $this;
    }

    /**
     * 采购推送给供应商等级
     * @param int $value
     * @return $this
     */
    public function setBuyPush(int $value): self
    {
        $this->buyPush = $value;

        return $this;
    }

    /**
     * 供应商推荐等级
     * @param int $value
     * @return $this
     */
    public function setSupplierPush(int $value): self
    {
        $this->supplierPush = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getAuId()
    {
        return $this->auId;
    }

    /**
     * 等级id
     * @return int
     */
    public function getLevelId()
    {
        return $this->levelId;
    }

    /**
     * 权威徽章
     * @return mixed
     */
    public function getHasBadge()
    {
        return $this->hasBadge;
    }

    /**
     * 商机推荐条数
     * @return int
     */
    public function getBusinessLimit()
    {
        return $this->businessLimit;
    }

    /**
     * 主动联系采购
     * @return int
     */
    public function getTakeContactDaily()
    {
        return $this->takeContactDaily;
    }

    /**
     * 产品详情设计
     * @return int
     */
    public function getProductDesign()
    {
        return $this->productDesign;
    }

    /**
     * 产品推广报名
     * @return int
     */
    public function getProductMarketing()
    {
        return $this->productMarketing;
    }

    /**
     * 活动报名特权
     * @return int
     */
    public function getEventRegister()
    {
        return $this->eventRegister;
    }

    /**
     * 店铺搜索排名
     * @return int
     */
    public function getShopRanking()
    {
        return $this->shopRanking;
    }

    /**
     * 产品搜索排名
     * @return int
     */
    public function getProductRanking()
    {
        return $this->productRanking;
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
     * 规则版本号
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * 权限使用状态:1正常0删除
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 担保交易0关闭，1开启
     * @return int
     */
    public function getGuaranteeTransaction()
    {
        return $this->guaranteeTransaction;
    }

    /**
     * 采购推送给供应商等级
     * @return int
     */
    public function getBuyPush()
    {
        return $this->buyPush;
    }

    /**
     * 供应商推荐等级
     * @return int
     */
    public function getSupplierPush()
    {
        return $this->supplierPush;
    }

}
