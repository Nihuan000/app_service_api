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
 * 采购搜索记录
 * @Entity()
 * @Table(name="sb_buy_search_log")
 * @uses      BuySearchLog
 */
class BuySearchLog extends Model
{
    /**
     * @var int $logId 
     * @Id()
     * @Column(name="log_id", type="integer")
     */
    private $logId;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $role 用户身份
     * @Column(name="role", type="tinyint", default=0)
     */
    private $role;

    /**
     * @var string $keyword 关键词
     * @Column(name="keyword", type="string", length=150, default="")
     */
    private $keyword;

    /**
     * @var int $areaId 区域id
     * @Column(name="area_id", type="integer", default=0)
     */
    private $areaId;

    /**
     * @var int $parentid 父分类id
     * @Column(name="parentid", type="integer", default=0)
     */
    private $parentid;

    /**
     * @var string $labelIds 标签筛选组
     * @Column(name="label_ids", type="string", length=150, default="")
     */
    private $labelIds;

    /**
     * @var int $isHot 0：智能排序，1：热门采购 2：最新发布 3:待抢单 5:全部采购 6:订阅标签推荐
     * @Column(name="is_hot", type="tinyint", default=0)
     */
    private $isHot;

    /**
     * @var int $isCustomize 是否接受定做 0：否，1：是
     * @Column(name="is_customize", type="tinyint", default=0)
     */
    private $isCustomize;

    /**
     * @var int $pageNum 当前结果第N页 默认为1
     * @Column(name="page_num", type="integer", default=1)
     */
    private $pageNum;

    /**
     * @var string $appVersion 搜布版本号
     * @Column(name="app_version", type="string", length=15, default="")
     */
    private $appVersion;

    /**
     * @var int $searchTime 搜索时间
     * @Column(name="search_time", type="integer", default=0)
     */
    private $searchTime;

    /**
     * @param int $value
     * @return $this
     */
    public function setLogId(int $value)
    {
        $this->logId = $value;

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
     * 用户身份
     * @param int $value
     * @return $this
     */
    public function setRole(int $value): self
    {
        $this->role = $value;

        return $this;
    }

    /**
     * 关键词
     * @param string $value
     * @return $this
     */
    public function setKeyword(string $value): self
    {
        $this->keyword = $value;

        return $this;
    }

    /**
     * 区域id
     * @param int $value
     * @return $this
     */
    public function setAreaId(int $value): self
    {
        $this->areaId = $value;

        return $this;
    }

    /**
     * 父分类id
     * @param int $value
     * @return $this
     */
    public function setParentid(int $value): self
    {
        $this->parentid = $value;

        return $this;
    }

    /**
     * 标签筛选组
     * @param string $value
     * @return $this
     */
    public function setLabelIds(string $value): self
    {
        $this->labelIds = $value;

        return $this;
    }

    /**
     * 0：智能排序，1：热门采购 2：最新发布
     * @param int $value
     * @return $this
     */
    public function setIsHot(int $value): self
    {
        $this->isHot = $value;

        return $this;
    }

    /**
     * 是否接受定做 0：否，1：是
     * @param int $value
     * @return $this
     */
    public function setIsCustomize(int $value): self
    {
        $this->isCustomize = $value;

        return $this;
    }

    /**
     * 当前结果第N页 默认为1
     * @param int $value
     * @return $this
     */
    public function setPageNum(int $value): self
    {
        $this->pageNum = $value;

        return $this;
    }

    /**
     * 搜布版本号
     * @param string $value
     * @return $this
     */
    public function setAppVersion(string $value): self
    {
        $this->appVersion = $value;

        return $this;
    }

    /**
     * 搜索时间
     * @param int $value
     * @return $this
     */
    public function setSearchTime(int $value): self
    {
        $this->searchTime = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogId()
    {
        return $this->logId;
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
     * 用户身份
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * 关键词
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * 区域id
     * @return int
     */
    public function getAreaId()
    {
        return $this->areaId;
    }

    /**
     * 父分类id
     * @return int
     */
    public function getParentid()
    {
        return $this->parentid;
    }

    /**
     * 标签筛选组
     * @return string
     */
    public function getLabelIds()
    {
        return $this->labelIds;
    }

    /**
     * 0：智能排序，1：热门采购 2：最新发布
     * @return int
     */
    public function getIsHot()
    {
        return $this->isHot;
    }

    /**
     * 是否接受定做 0：否，1：是
     * @return int
     */
    public function getIsCustomize()
    {
        return $this->isCustomize;
    }

    /**
     * 当前结果第N页 默认为1
     * @return mixed
     */
    public function getPageNum()
    {
        return $this->pageNum;
    }

    /**
     * 搜布版本号
     * @return string
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * 搜索时间
     * @return int
     */
    public function getSearchTime()
    {
        return $this->searchTime;
    }

}
