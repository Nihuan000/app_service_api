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
 * @Entity()
 * @Table(name="tb_push_buy_record")
 * @uses      TbPushBuyRecord
 */
class TbPushBuyRecord extends Model
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
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var string $uuid 唯一标识
     * @Column(name="uuid", type="string", length=50)
     * @Required()
     */
    private $uuid;

    /**
     * @var int $isRead 是否有点击
     * @Column(name="is_read", type="integer", default=0)
     */
    private $isRead;

    /**
     * @var int $isUseful 是否有用 0:无, 1:有
     * @Column(name="is_useful", type="integer", default=0)
     */
    private $isUseful;

    /**
     * @var int $isMatch 推送是否匹配 0:否 1:是
     * @Column(name="is_match", type="tinyint", default=1)
     */
    private $isMatch;

    /**
     * @var int $orderId 是否转换为订单 0:没有, 其他:订单id
     * @Column(name="order_id", type="integer", default=0)
     */
    private $orderId;

    /**
     * @var int $isPush 是否已推送 0:否 1:是
     * @Column(name="is_push", type="tinyint", default=1)
     */
    private $isPush;

    /**
     * @var int $pushHour 推送时间  0:实时 >0 指定N时
     * @Column(name="push_hour", type="integer", default=0)
     */
    private $pushHour;

    /**
     * @var int $dayTime 添加当天时间
     * @Column(name="day_time", type="integer", default=0)
     */
    private $dayTime;

    /**
     * @var int $updateTime 修改时间
     * @Column(name="update_time", type="bigint", default=0)
     */
    private $updateTime;

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
     * 唯一标识
     * @param string $value
     * @return $this
     */
    public function setUuid(string $value): self
    {
        $this->uuid = $value;

        return $this;
    }

    /**
     * 是否有点击
     * @param int $value
     * @return $this
     */
    public function setIsRead(int $value): self
    {
        $this->isRead = $value;

        return $this;
    }

    /**
     * 是否有用 0:无, 1:有
     * @param int $value
     * @return $this
     */
    public function setIsUseful(int $value): self
    {
        $this->isUseful = $value;

        return $this;
    }

    /**
     * 推送是否匹配 0:否 1:是
     * @param int $value
     * @return $this
     */
    public function setIsMatch(int $value): self
    {
        $this->isMatch = $value;

        return $this;
    }

    /**
     * 是否转换为订单 0:没有, 其他:订单id
     * @param int $value
     * @return $this
     */
    public function setOrderId(int $value): self
    {
        $this->orderId = $value;

        return $this;
    }

    /**
     * 是否已推送 0:否 1:是
     * @param int $value
     * @return $this
     */
    public function setIsPush(int $value): self
    {
        $this->isPush = $value;

        return $this;
    }

    /**
     * 推送时间  0:实时 >0 指定N时
     * @param int $value
     * @return $this
     */
    public function setPushHour(int $value): self
    {
        $this->pushHour = $value;

        return $this;
    }

    /**
     * 添加当天时间
     * @param int $value
     * @return $this
     */
    public function setDayTime(int $value): self
    {
        $this->dayTime = $value;

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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 采购id
     * @return mixed
     */
    public function getBuyId()
    {
        return $this->buyId;
    }

    /**
     * 用户id
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 唯一标识
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * 是否有点击
     * @return int
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * 是否有用 0:无, 1:有
     * @return int
     */
    public function getIsUseful()
    {
        return $this->isUseful;
    }

    /**
     * 推送是否匹配 0:否 1:是
     * @return mixed
     */
    public function getIsMatch()
    {
        return $this->isMatch;
    }

    /**
     * 是否转换为订单 0:没有, 其他:订单id
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * 是否已推送 0:否 1:是
     * @return mixed
     */
    public function getIsPush()
    {
        return $this->isPush;
    }

    /**
     * 推送时间  0:实时 >0 指定N时
     * @return int
     */
    public function getPushHour()
    {
        return $this->pushHour;
    }

    /**
     * 添加当天时间
     * @return int
     */
    public function getDayTime()
    {
        return $this->dayTime;
    }

    /**
     * 修改时间
     * @return int
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

}
