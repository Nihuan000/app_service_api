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
 * 采购属性表
 * @Entity()
 * @Table(name="sb_buy_attribute")
 * @uses      BuyAttribute
 */
class BuyAttribute extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var int $buyId 采购id
     * @Column(name="buy_id", type="integer", default=0)
     */
    private $buyId;

    /**
     * @var string $uses 用途
     * @Column(name="uses", type="string", length=150, default="''")
     */
    private $uses;

    /**
     * @var int $soft 1:挺阔2:适中3:柔软
     * @Column(name="soft", type="tinyint", default=0)
     */
    private $soft;

    /**
     * @var int $inteScore 完整度评分 默认0,满分10分
     * @Column(name="inte_score", type="tinyint", default=0)
     */
    private $inteScore;

    /**
     * @var float $operationSecond 发布采购所用时间
     * @Column(name="operation_second", type="float", default=0)
     */
    private $operationSecond;

    /**
     * @var int $offerCount 报价条数
     * @Column(name="offer_count", type="integer", default=0)
     */
    private $offerCount;

    /**
     * @var int $pendingOfferCount 待处理报价数量
     * @Column(name="pending_offer_count", type="smallint", default=0)
     */
    private $pendingOfferCount;

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
     * 采购id
     * @param int $value
     * @return $this
     */
    public function setBuyId(int $value): self
    {
        $this->buyId = $value;

        return $this;
    }

    /**
     * 用途
     * @param string $value
     * @return $this
     */
    public function setUses(string $value): self
    {
        $this->uses = $value;

        return $this;
    }

    /**
     * 1:挺阔2:适中3:柔软
     * @param int $value
     * @return $this
     */
    public function setSoft(int $value): self
    {
        $this->soft = $value;

        return $this;
    }

    /**
     * 完整度评分 默认0,满分10分
     * @param int $value
     * @return $this
     */
    public function setInteScore(int $value): self
    {
        $this->inteScore = $value;

        return $this;
    }

    /**
     * 发布采购所用时间
     * @param float $value
     * @return $this
     */
    public function setOperationSecond(float $value): self
    {
        $this->operationSecond = $value;

        return $this;
    }

    /**
     * 报价条数
     * @param int $value
     * @return $this
     */
    public function setOfferCount(int $value): self
    {
        $this->offerCount = $value;

        return $this;
    }

    /**
     * 待处理报价数量
     * @param int $value
     * @return $this
     */
    public function setPendingOfferCount(int $value): self
    {
        $this->pendingOfferCount = $value;

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
     * 采购id
     * @return int
     */
    public function getBuyId()
    {
        return $this->buyId;
    }

    /**
     * 用途
     * @return mixed
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * 1:挺阔2:适中3:柔软
     * @return int
     */
    public function getSoft()
    {
        return $this->soft;
    }

    /**
     * 完整度评分 默认0,满分10分
     * @return int
     */
    public function getInteScore()
    {
        return $this->inteScore;
    }

    /**
     * 发布采购所用时间
     * @return float
     */
    public function getOperationSecond()
    {
        return $this->operationSecond;
    }

    /**
     * 报价条数
     * @return int
     */
    public function getOfferCount()
    {
        return $this->offerCount;
    }

    /**
     * 待处理报价数量
     * @return int
     */
    public function getPendingOfferCount()
    {
        return $this->pendingOfferCount;
    }

}
