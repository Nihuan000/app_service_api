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
 * 产品信息访问记录表

 * @Entity()
 * @Table(name="sb_product_records")
 * @uses      ProductRecords
 */
class ProductRecords extends Model
{
    /**
     * @var int $rId 
     * @Id()
     * @Column(name="r_id", type="integer")
     */
    private $rId;

    /**
     * @var int $userId 访问者id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $proId 产品id
     * @Column(name="pro_id", type="integer", default=0)
     */
    private $proId;

    /**
     * @var int $rTime 访问时间
     * @Column(name="r_time", type="integer", default=0)
     */
    private $rTime;

    /**
     * @var int $fromType 0:app1:Android 2:ios 3:微信 10微信
     * @Column(name="from_type", type="tinyint", default=0)
     */
    private $fromType;

    /**
     * @var int $scene 0 其他 1 网页 2 扫码 3 消息(活动) 4 搜索 5 微信 6智能找布 7店铺 8推送 9 banner广告 10我的产品 11系统消息 12搜布助手 13聊天 14优惠券店铺 15优惠券产品 16收藏 17评价 18足迹 19智能匹配 20报价详情 21结束找布 22最近浏览 23购物车 24买入订单 25买入详情 26售出详情 27支付成功 28评价详情 29交易成功 30同店产品/邻家好 31 搜索综合排序, 32 搜索热门,33 搜索最新,34 搜索筛选, 35 搜索价格最低，36 搜索价格最高
     * @Column(name="scene", type="smallint")
     */
    private $scene;

    /**
     * @var int $business 0:其他1：首页21：消息
     * @Column(name="business", type="tinyint", default=0)
     */
    private $business;

    /**
     * @var int $isFilter 0:老的查看记录 1:无关键词,无筛选项 2:无关键词,有筛选项 3:有关键词,无筛选项 4:有关键词,有筛选项
     * @Column(name="is_filter", type="tinyint", default=0)
     */
    private $isFilter;

    /**
     * @var string $requestId 请求唯一标识,关联search_record表
     * @Column(name="request_id", type="string", length=32, default="")
     */
    private $requestId;

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
     * 产品id
     * @param int $value
     * @return $this
     */
    public function setProId(int $value): self
    {
        $this->proId = $value;

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
     * 0:app1:Android 2:ios 3:微信 10微信
     * @param int $value
     * @return $this
     */
    public function setFromType(int $value): self
    {
        $this->fromType = $value;

        return $this;
    }

    /**
     * 0 其他 1 网页 2 扫码 3 消息(活动) 4 搜索 5 微信 6智能找布 7店铺 8推送 9 banner广告 10我的产品 11系统消息 12搜布助手 13聊天 14优惠券店铺 15优惠券产品 16收藏 17评价 18足迹 19智能匹配 20报价详情 21结束找布 22最近浏览 23购物车 24买入订单 25买入详情 26售出详情 27支付成功 28评价详情 29交易成功 30同店产品/邻家好 31 搜索综合排序, 32 搜索热门,33 搜索最新,34 搜索筛选, 35 搜索价格最低，36 搜索价格最高
     * @param int $value
     * @return $this
     */
    public function setScene(int $value): self
    {
        $this->scene = $value;

        return $this;
    }

    /**
     * 0:其他1：首页21：消息
     * @param int $value
     * @return $this
     */
    public function setBusiness(int $value): self
    {
        $this->business = $value;

        return $this;
    }

    /**
     * 0:老的查看记录 1:无关键词,无筛选项 2:无关键词,有筛选项 3:有关键词,无筛选项 4:有关键词,有筛选项
     * @param int $value
     * @return $this
     */
    public function setIsFilter(int $value): self
    {
        $this->isFilter = $value;

        return $this;
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
     * 产品id
     * @return int
     */
    public function getProId()
    {
        return $this->proId;
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
     * 0:app1:Android 2:ios 3:微信 10微信
     * @return int
     */
    public function getFromType()
    {
        return $this->fromType;
    }

    /**
     * 0 其他 1 网页 2 扫码 3 消息(活动) 4 搜索 5 微信 6智能找布 7店铺 8推送 9 banner广告 10我的产品 11系统消息 12搜布助手 13聊天 14优惠券店铺 15优惠券产品 16收藏 17评价 18足迹 19智能匹配 20报价详情 21结束找布 22最近浏览 23购物车 24买入订单 25买入详情 26售出详情 27支付成功 28评价详情 29交易成功 30同店产品/邻家好 31 搜索综合排序, 32 搜索热门,33 搜索最新,34 搜索筛选, 35 搜索价格最低，36 搜索价格最高
     * @return int
     */
    public function getScene()
    {
        return $this->scene;
    }

    /**
     * 0:其他1：首页21：消息
     * @return int
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * 0:老的查看记录 1:无关键词,无筛选项 2:无关键词,有筛选项 3:有关键词,无筛选项 4:有关键词,有筛选项
     * @return int
     */
    public function getIsFilter()
    {
        return $this->isFilter;
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
