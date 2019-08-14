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
 * 产品搜索记录表
 * @Entity()
 * @Table(name="sb_product_search_log")
 * @uses      ProductSearchLog
 */
class ProductSearchLog extends Model
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
     * @var string $keyword 搜索关键词
     * @Column(name="keyword", type="string", length=150, default="")
     */
    private $keyword;

    /**
     * @var int $areaId 区域id
     * @Column(name="area_id", type="integer", default=0)
     */
    private $areaId;

    /**
     * @var int $isNew  0:智能排序 1:新发布 2:热门产品 4:首页推荐
     * @Column(name="is_new", type="tinyint", default=0)
     */
    private $isNew;

    /**
     * @var string $season 季节选择
     * @Column(name="season", type="string", length=15, default="")
     */
    private $season;

    /**
     * @var int $usesTop 用途大类
     * @Column(name="uses_top", type="integer", default=0)
     */
    private $usesTop;

    /**
     * @var int $usesId 用途小类
     * @Column(name="uses_id", type="integer", default=0)
     */
    private $usesId;

    /**
     * @var int $priceOrder 价格排序 1:价格从低到高 2：价格从高到低
     * @Column(name="price_order", type="tinyint", default=0)
     */
    private $priceOrder;

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
     * @var int $matchNum 匹配数
     * @Column(name="match_num", type="integer", default=0)
     */
    private $matchNum;

    /**
     * @var string $matchIds 匹配产品列表
     * @Column(name="match_ids", type="string", length=455, default="")
     */
    private $matchIds;

    /**
     * @var string $requestId 请求唯一标识
     * @Column(name="request_id", type="string", length=32, default="")
     */
    private $requestId;

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
     * 搜索关键词
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
     *  0:智能排序 1:新发布 2:热门产品 4:首页推荐
     * @param int $value
     * @return $this
     */
    public function setIsNew(int $value): self
    {
        $this->isNew = $value;

        return $this;
    }

    /**
     * 季节选择
     * @param string $value
     * @return $this
     */
    public function setSeason(string $value): self
    {
        $this->season = $value;

        return $this;
    }

    /**
     * 用途大类
     * @param int $value
     * @return $this
     */
    public function setUsesTop(int $value): self
    {
        $this->usesTop = $value;

        return $this;
    }

    /**
     * 用途小类
     * @param int $value
     * @return $this
     */
    public function setUsesId(int $value): self
    {
        $this->usesId = $value;

        return $this;
    }

    /**
     * 价格排序 1:价格从低到高 2：价格从高到低
     * @param int $value
     * @return $this
     */
    public function setPriceOrder(int $value): self
    {
        $this->priceOrder = $value;

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
     * 匹配数
     * @param int $value
     * @return $this
     */
    public function setMatchNum(int $value): self
    {
        $this->matchNum = $value;

        return $this;
    }

    /**
     * 匹配产品列表
     * @param string $value
     * @return $this
     */
    public function setMatchIds(string $value): self
    {
        $this->matchIds = $value;

        return $this;
    }

    /**
     * 请求唯一标识
     * @param string $value
     * @return $this
     */
    public function setRequestId(string $value): self
    {
        $this->requestId = $value;

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
     * 搜索关键词
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
     *  0:智能排序 1:新发布 2:热门产品 4:首页推荐
     * @return int
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * 季节选择
     * @return string
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * 用途大类
     * @return int
     */
    public function getUsesTop()
    {
        return $this->usesTop;
    }

    /**
     * 用途小类
     * @return int
     */
    public function getUsesId()
    {
        return $this->usesId;
    }

    /**
     * 价格排序 1:价格从低到高 2：价格从高到低
     * @return int
     */
    public function getPriceOrder()
    {
        return $this->priceOrder;
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

    /**
     * 匹配数
     * @return int
     */
    public function getMatchNum()
    {
        return $this->matchNum;
    }

    /**
     * 匹配产品列表
     * @return string
     */
    public function getMatchIds()
    {
        return $this->matchIds;
    }

    /**
     * 请求唯一标识
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

}
