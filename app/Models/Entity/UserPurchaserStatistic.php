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
 * 采购商信息统计
 * @Entity()
 * @Table(name="sb_user_purchaser_statistic")
 * @uses      UserPurchaserStatistic
 */
class UserPurchaserStatistic extends Model
{
    /**
     * @var int $bsId 
     * @Id()
     * @Column(name="bs_id", type="integer")
     */
    private $bsId;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var string $purchaseLabel 采购偏好
     * @Column(name="purchase_label", type="string", length=155, default="")
     */
    private $purchaseLabel;

    /**
     * @var float $orderTotal 近30天采购总额
     * @Column(name="order_total", type="decimal", default=0)
     */
    private $orderTotal;

    /**
     * @var float $order60Total 60天采购额
     * @Column(name="order_60_total", type="decimal", default=0)
     */
    private $order60Total;

    /**
     * @var float $order90Total 90天采购额
     * @Column(name="order_90_total", type="decimal", default=0)
     */
    private $order90Total;

    /**
     * @var int $purchaseTimes 近30天采购频次
     * @Column(name="purchase_times", type="integer", default=0)
     */
    private $purchaseTimes;

    /**
     * @var int $purchaser60Times 60天采购频次
     * @Column(name="purchaser_60_times", type="integer", default=0)
     */
    private $purchaser60Times;

    /**
     * @var int $purchaser90Times 90天采购频次
     * @Column(name="purchaser_90_times", type="integer", default=0)
     */
    private $purchaser90Times;

    /**
     * @var string $onlineShopList 网店列表
     * @Column(name="online_shop_list", type="string", length=255, default="")
     */
    private $onlineShopList;

    /**
     * @var string $purchaseIdentity 采购身份
     * @Column(name="purchase_identity", type="string", length=65, default="")
     */
    private $purchaseIdentity;

    /**
     * @var string $addDate 统计日期
     * @Column(name="add_date", type="date")
     * @Required()
     */
    private $addDate;

    /**
     * @var int $recordTime 记录生成时间
     * @Column(name="record_time", type="integer", default=0)
     */
    private $recordTime;

    /**
     * @param int $value
     * @return $this
     */
    public function setBsId(int $value)
    {
        $this->bsId = $value;

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
     * 采购偏好
     * @param string $value
     * @return $this
     */
    public function setPurchaseLabel(string $value): self
    {
        $this->purchaseLabel = $value;

        return $this;
    }

    /**
     * 近30天采购总额
     * @param float $value
     * @return $this
     */
    public function setOrderTotal(float $value): self
    {
        $this->orderTotal = $value;

        return $this;
    }

    /**
     * 60天采购额
     * @param float $value
     * @return $this
     */
    public function setOrder60Total(float $value): self
    {
        $this->order60Total = $value;

        return $this;
    }

    /**
     * 90天采购额
     * @param float $value
     * @return $this
     */
    public function setOrder90Total(float $value): self
    {
        $this->order90Total = $value;

        return $this;
    }

    /**
     * 近30天采购频次
     * @param int $value
     * @return $this
     */
    public function setPurchaseTimes(int $value): self
    {
        $this->purchaseTimes = $value;

        return $this;
    }

    /**
     * 60天采购频次
     * @param int $value
     * @return $this
     */
    public function setPurchaser60Times(int $value): self
    {
        $this->purchaser60Times = $value;

        return $this;
    }

    /**
     * 90天采购频次
     * @param int $value
     * @return $this
     */
    public function setPurchaser90Times(int $value): self
    {
        $this->purchaser90Times = $value;

        return $this;
    }

    /**
     * 网店列表
     * @param string $value
     * @return $this
     */
    public function setOnlineShopList(string $value): self
    {
        $this->onlineShopList = $value;

        return $this;
    }

    /**
     * 采购身份
     * @param string $value
     * @return $this
     */
    public function setPurchaseIdentity(string $value): self
    {
        $this->purchaseIdentity = $value;

        return $this;
    }

    /**
     * 统计日期
     * @param string $value
     * @return $this
     */
    public function setAddDate(string $value): self
    {
        $this->addDate = $value;

        return $this;
    }

    /**
     * 记录生成时间
     * @param int $value
     * @return $this
     */
    public function setRecordTime(int $value): self
    {
        $this->recordTime = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBsId()
    {
        return $this->bsId;
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
     * 采购偏好
     * @return string
     */
    public function getPurchaseLabel()
    {
        return $this->purchaseLabel;
    }

    /**
     * 近30天采购总额
     * @return mixed
     */
    public function getOrderTotal()
    {
        return $this->orderTotal;
    }

    /**
     * 60天采购额
     * @return mixed
     */
    public function getOrder60Total()
    {
        return $this->order60Total;
    }

    /**
     * 90天采购额
     * @return mixed
     */
    public function getOrder90Total()
    {
        return $this->order90Total;
    }

    /**
     * 近30天采购频次
     * @return int
     */
    public function getPurchaseTimes()
    {
        return $this->purchaseTimes;
    }

    /**
     * 60天采购频次
     * @return int
     */
    public function getPurchaser60Times()
    {
        return $this->purchaser60Times;
    }

    /**
     * 90天采购频次
     * @return int
     */
    public function getPurchaser90Times()
    {
        return $this->purchaser90Times;
    }

    /**
     * 网店列表
     * @return string
     */
    public function getOnlineShopList()
    {
        return $this->onlineShopList;
    }

    /**
     * 采购身份
     * @return string
     */
    public function getPurchaseIdentity()
    {
        return $this->purchaseIdentity;
    }

    /**
     * 统计日期
     * @return string
     */
    public function getAddDate()
    {
        return $this->addDate;
    }

    /**
     * 记录生成时间
     * @return int
     */
    public function getRecordTime()
    {
        return $this->recordTime;
    }

}
