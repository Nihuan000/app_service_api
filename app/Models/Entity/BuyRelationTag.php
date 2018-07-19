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
 * 采购标签关联表
 * @Entity()
 * @Table(name="sb_buy_relation_tag")
 * @uses      BuyRelationTag
 */
class BuyRelationTag extends Model
{
    /**
     * @var int $relationId 
     * @Id()
     * @Column(name="relation_id", type="integer")
     */
    private $relationId;

    /**
     * @var int $buyId 采购 id
     * @Column(name="buy_id", type="integer", default=0)
     */
    private $buyId;

    /**
     * @var int $tagId 标签id
     * @Column(name="tag_id", type="integer", default=0)
     */
    private $tagId;

    /**
     * @var string $tagName 标签名称
     * @Column(name="tag_name", type="string", length=55, default="''")
     */
    private $tagName;

    /**
     * @var int $parentId 标签的父id
     * @Column(name="parent_id", type="integer", default=0)
     */
    private $parentId;

    /**
     * @var string $parentName 父级标签名称
     * @Column(name="parent_name", type="string", length=55, default="''")
     */
    private $parentName;

    /**
     * @var int $topId 顶级类ID
     * @Column(name="top_id", type="integer", default=0)
     */
    private $topId;

    /**
     * @var string $topName 顶级类标签名称
     * @Column(name="top_name", type="string", length=55, default="''")
     */
    private $topName;

    /**
     * @var int $cateId 归属类 1:品名 2:成分 4: 工艺 5:用途
     * @Column(name="cate_id", type="integer", default=0)
     */
    private $cateId;

    /**
     * @param int $value
     * @return $this
     */
    public function setRelationId(int $value)
    {
        $this->relationId = $value;

        return $this;
    }

    /**
     * 采购 id
     * @param int $value
     * @return $this
     */
    public function setBuyId(int $value): self
    {
        $this->buyId = $value;

        return $this;
    }

    /**
     * 标签id
     * @param int $value
     * @return $this
     */
    public function setTagId(int $value): self
    {
        $this->tagId = $value;

        return $this;
    }

    /**
     * 标签名称
     * @param string $value
     * @return $this
     */
    public function setTagName(string $value): self
    {
        $this->tagName = $value;

        return $this;
    }

    /**
     * 标签的父id
     * @param int $value
     * @return $this
     */
    public function setParentId(int $value): self
    {
        $this->parentId = $value;

        return $this;
    }

    /**
     * 父级标签名称
     * @param string $value
     * @return $this
     */
    public function setParentName(string $value): self
    {
        $this->parentName = $value;

        return $this;
    }

    /**
     * 顶级类ID
     * @param int $value
     * @return $this
     */
    public function setTopId(int $value): self
    {
        $this->topId = $value;

        return $this;
    }

    /**
     * 顶级类标签名称
     * @param string $value
     * @return $this
     */
    public function setTopName(string $value): self
    {
        $this->topName = $value;

        return $this;
    }

    /**
     * 归属类 1:品名 2:成分 4: 工艺 5:用途
     * @param int $value
     * @return $this
     */
    public function setCateId(int $value): self
    {
        $this->cateId = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelationId()
    {
        return $this->relationId;
    }

    /**
     * 采购 id
     * @return int
     */
    public function getBuyId()
    {
        return $this->buyId;
    }

    /**
     * 标签id
     * @return int
     */
    public function getTagId()
    {
        return $this->tagId;
    }

    /**
     * 标签名称
     * @return mixed
     */
    public function getTagName()
    {
        return $this->tagName;
    }

    /**
     * 标签的父id
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * 父级标签名称
     * @return mixed
     */
    public function getParentName()
    {
        return $this->parentName;
    }

    /**
     * 顶级类ID
     * @return int
     */
    public function getTopId()
    {
        return $this->topId;
    }

    /**
     * 顶级类标签名称
     * @return mixed
     */
    public function getTopName()
    {
        return $this->topName;
    }

    /**
     * 归属类 1:品名 2:成分 4: 工艺 5:用途
     * @return int
     */
    public function getCateId()
    {
        return $this->cateId;
    }

}
