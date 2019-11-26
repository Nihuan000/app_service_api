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
 * 采购信息访问记录表
 * @Entity()
 * @Table(name="sb_buy_records")
 * @uses      BuyRecords
 */
class BuyRecords extends Model
{
    /**
     * @var int $rId
     * @Id()
     * @Column(name="r_id", type="integer")
     */
    private $rId;

    /**
     * @var int $userId 访问者id
     * @Column(name="user_id", type="integer")
     * @Required()
     */
    private $userId;

    /**
     * @var int $buyId 采购id
     * @Column(name="buy_id", type="integer")
     * @Required()
     */
    private $buyId;

    /**
     * @var int $rTime 访问时间
     * @Column(name="r_time", type="integer")
     * @Required()
     */
    private $rTime;

    /**
     * @var int $scene 1:首页 2:商机推荐 3:最新采购 4:综合排序采购 5:无报价采购 6:我的采购 7:他的采购 8:浏览记录 9:我的收藏 10:报价详情 11:同类采购推荐 12:外部 URL 13:通知 14:聊天
     * @Column(name="scene", type="smallint")
     * @Required()
     */
    private $scene;

    /**
     * @var int $isFilter 0:旧版本 1:无关键词,无筛选项 2:无关键词,有筛选项 3:有关键词,无筛选项 4:有关键词,有筛选项
     * @Column(name="is_filter", type="tinyint")
     * @Required()
     */
    private $isFilter;

    /**
     * @var string $requestId 请求唯一标识,关联search_record表
     * @Column(name="request_id", type="string", length=32, default="")
     */
    private $requestId;

    /**
     * @var int $fromType 0:app1:Android 2:ios 3:微信
     * @Column(name="from_type", type="tinyint", default=0)
     */
    private $fromType;

    /**
     * @param int $value
     * @return $this
     */
    public function setRId(int $value)
    {
        $this->rId = $value;

        return $this;
    }

    /**
     * 访问者id
     * @param int $value
     * @return $this
     */
    public function setUserId(int $value): self
    {
        $this->userId = $value;

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
     * 访问时间
     * @param int $value
     * @return $this
     */
    public function setRTime(int $value): self
    {
        $this->rTime = $value;

        return $this;
    }

    /**
     * 1:首页 2:商机推荐 3:最新采购 4:综合排序采购 5:无报价采购 6:我的采购 7:他的采购 8:浏览记录 9:我的收藏 10:报价详情 11:同类采购推荐 12:外部 URL 13:通知 14:聊天
     * @param int $value
     * @return $this
     */
    public function setScene(int $value): self
    {
        $this->scene = $value;

        return $this;
    }

    /**
     * 0:旧版本 1:无关键词,无筛选项 2:无关键词,有筛选项 3:有关键词,无筛选项 4:有关键词,有筛选项
     * @param int $value
     * @return $this
     */
    public function setIsFilter(int $value): self
    {
        $this->isFilter = $value;

        return $this;
    }

    /**
     * 0:app1:Android 2:ios 3:微信
     * @param int $value
     * @return $this
     */
    public function setFromType(int $value): self
    {
        $this->fromType = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRId()
    {
        return $this->rId;
    }

    /**
     * 访问者id
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
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
     * 访问时间
     * @return int
     */
    public function getRTime()
    {
        return $this->rTime;
    }

    /**
     * 1:首页 2:商机推荐 3:最新采购 4:综合排序采购 5:无报价采购 6:我的采购 7:他的采购 8:浏览记录 9:我的收藏 10:报价详情 11:同类采购推荐 12:外部 URL 13:通知 14:聊天
     * @return int
     */
    public function getScene()
    {
        return $this->scene;
    }

    /**
     * 0:旧版本 1:无关键词,无筛选项 2:无关键词,有筛选项 3:有关键词,无筛选项 4:有关键词,有筛选项
     * @return int
     */
    public function getIsFilter()
    {
        return $this->isFilter;
    }

    /**
     * 0:app1:Android 2:ios 3:微信
     * @return int
     */
    public function getFromType()
    {
        return $this->fromType;
    }


    /**
     * 请求唯一标识,关联search_record表
     * @param string $value
     * @return $this
     */
    public function setRequestId(string $value): self
    {
        $this->requestId = $value;

        return $this;
    }

    /**
     * 请求唯一标识,关联search_record表
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

}
