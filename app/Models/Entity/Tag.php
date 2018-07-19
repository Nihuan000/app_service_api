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
 * 标签表
 * @Entity()
 * @Table(name="sb_tag")
 * @uses      Tag
 */
class Tag extends Model
{
    /**
     * @var int $tagId 
     * @Id()
     * @Column(name="tag_id", type="integer")
     */
    private $tagId;

    /**
     * @var string $name 标签名
     * @Column(name="name", type="string", length=55, default="''")
     */
    private $name;

    /**
     * @var int $parentId 父级id 0为第二级 -1为公共属性
     * @Column(name="parent_id", type="integer", default=0)
     */
    private $parentId;

    /**
     * @var int $topId 顶级类ID
     * @Column(name="top_id", type="integer", default=0)
     */
    private $topId;

    /**
     * @var int $cateId 归属类 1:品名 2:成分 4: 工艺 5:用途
     * @Column(name="cate_id", type="tinyint", default=0)
     */
    private $cateId;

    /**
     * @var int $status 启用状态 0:停用 1:启用
     * @Column(name="status", type="tinyint", default=1)
     */
    private $status;

    /**
     * @var int $sorting 排序 越小越靠前
     * @Column(name="sorting", type="tinyint", default=99)
     */
    private $sorting;

    /**
     * @param int $value
     * @return $this
     */
    public function setTagId(int $value)
    {
        $this->tagId = $value;

        return $this;
    }

    /**
     * 标签名
     * @param string $value
     * @return $this
     */
    public function setName(string $value): self
    {
        $this->name = $value;

        return $this;
    }

    /**
     * 父级id 0为第二级 -1为公共属性
     * @param int $value
     * @return $this
     */
    public function setParentId(int $value): self
    {
        $this->parentId = $value;

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
     * 启用状态 0:停用 1:启用
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * 排序 越小越靠前
     * @param int $value
     * @return $this
     */
    public function setSorting(int $value): self
    {
        $this->sorting = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTagId()
    {
        return $this->tagId;
    }

    /**
     * 标签名
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 父级id 0为第二级 -1为公共属性
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
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
     * 归属类 1:品名 2:成分 4: 工艺 5:用途
     * @return int
     */
    public function getCateId()
    {
        return $this->cateId;
    }

    /**
     * 启用状态 0:停用 1:启用
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 排序 越小越靠前
     * @return mixed
     */
    public function getSorting()
    {
        return $this->sorting;
    }

}
