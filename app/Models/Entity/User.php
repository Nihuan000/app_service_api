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
 * 用户信息表(店铺信息)
 * @Entity()
 * @Table(name="sb_user")
 * @uses      User
 */
class User extends Model
{
    /**
     * @var int $userId 
     * @Id()
     * @Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string $name 用户名称
     * @Column(name="name", type="string", length=100, default="''")
     */
    private $name;

    /**
     * @var string $portrait 头像
     * @Column(name="portrait", type="string", length=100, default="''")
     */
    private $portrait;

    /**
     * @var string $background 背景图
     * @Column(name="background", type="string", length=100, default="''")
     */
    private $background;

    /**
     * @var string $phone 手机号码(登录名)
     * @Column(name="phone", type="string", length=11, default="''")
     */
    private $phone;

    /**
     * @var string $password 密码
     * @Column(name="password", type="string", length=45, default="''")
     */
    private $password;

    /**
     * @var int $provinceId 省份ID
     * @Column(name="province_id", type="integer", default=0)
     */
    private $provinceId;

    /**
     * @var string $province 省份
     * @Column(name="province", type="string", length=10, default="''")
     */
    private $province;

    /**
     * @var string $cityId 城市ID
     * @Column(name="city_id", type="string", length=45, default="'0'")
     */
    private $cityId;

    /**
     * @var string $city 城市
     * @Column(name="city", type="string", length=10, default="''")
     */
    private $city;

    /**
     * @var int $areaId 区ID,无效字段
     * @Column(name="area_id", type="integer", default=0)
     */
    private $areaId;

    /**
     * @var string $area 区,无效字段
     * @Column(name="area", type="string", length=10, default="''")
     */
    private $area;

    /**
     * @var string $detailAddress 详细地址
     * @Column(name="detail_address", type="string", length=100, default="''")
     */
    private $detailAddress;

    /**
     * @var string $mainProduct 主营产品
     * @Column(name="main_product", type="string", length=255, default="''")
     */
    private $mainProduct;

    /**
     * @var string $cid 与我相关推送标识id
     * @Column(name="cid", type="string", length=150, default="''")
     */
    private $cid;

    /**
     * @var string $mainProductKeyword 主营分词
     * @Column(name="main_product_keyword", type="string", length=1000, default="''")
     */
    private $mainProductKeyword;

    /**
     * @var int $status 用户状态 1 正常 0 锁定
     * @Column(name="status", type="tinyint", default=1)
     */
    private $status;

    /**
     * @var string $lockedCause 用户锁定原因
     * @Column(name="locked_cause", type="string", length=200, default="''")
     */
    private $lockedCause;

    /**
     * @var int $verifyPhone 手机验证 0未验证 1已验证
     * @Column(name="verify_phone", type="tinyint", default=0)
     */
    private $verifyPhone;

    /**
     * @var int $phoneType 手机类型 1安卓 2 IOS
     * @Column(name="phone_type", type="tinyint", default=0)
     */
    private $phoneType;

    /**
     * @var int $vip 是否是vip 0否 1是,无效字段
     * @Column(name="vip", type="tinyint", default=0)
     */
    private $vip;

    /**
     * @var int $ka 是否是ka供应商 0否 1是,无效字段
     * @Column(name="ka", type="tinyint", default=0)
     */
    private $ka;

    /**
     * @var int $certificationType 认证类型 0 未认证 1个人 2企业
     * @Column(name="certification_type", type="tinyint", default=0)
     */
    private $certificationType;

    /**
     * @var int $purchaser 认证采购商 0否 1是
     * @Column(name="purchaser", type="tinyint", default=0)
     */
    private $purchaser;

    /**
     * @var int $supplier 认证供应商 0否 1是
     * @Column(name="supplier", type="tinyint", default=0)
     */
    private $supplier;

    /**
     * @var int $clicks 店铺点击量
     * @Column(name="clicks", type="integer", default=0)
     */
    private $clicks;

    /**
     * @var int $buyCount 发布求购量
     * @Column(name="buy_count", type="integer", default=0)
     */
    private $buyCount;

    /**
     * @var int $productCount 发布产品量
     * @Column(name="product_count", type="integer", default=0)
     */
    private $productCount;

    /**
     * @var string $openid 微信ID
     * @Column(name="openid", type="string", length=255, default="''")
     */
    private $openid;

    /**
     * @var string $appOpenid app登陆后openid
     * @Column(name="app_openid", type="string", length=100, default="''")
     */
    private $appOpenid;

    /**
     * @var int $loginTimes 登陆次数
     * @Column(name="login_times", type="integer", default=0)
     */
    private $loginTimes;

    /**
     * @var int $role 用户角色:1.采购商2.面料供应商3.辅料供应商4.加工/服务
     * @Column(name="role", type="tinyint", default=0)
     */
    private $role;

    /**
     * @var int $parentId 主账号ID 如果没有主账号为0
     * @Column(name="parent_id", type="integer", default=0)
     */
    private $parentId;

    /**
     * @var int $isTourist 游客身份? 1:是 0:否
     * @Column(name="is_tourist", type="tinyint", default=0)
     */
    private $isTourist;

    /**
     * @var int $regTime 注册时间
     * @Column(name="reg_time", type="integer", default=0)
     */
    private $regTime;

    /**
     * @var string $device 设备号
     * @Column(name="device", type="char", length=64, default="''")
     */
    private $device;

    /**
     * @var int $fromType 用户来源 0 公众号 1安卓 2ios 3安卓微信 4ios微信 5后台 6公众号注册后登陆app 7 安卓公众号注册后登陆app 8 安卓公众号注册后登陆app
     * @Column(name="from_type", type="integer")
     * @Required()
     */
    private $fromType;

    /**
     * @var int $getAboutTime 获取最新与我相关条数的最后时间
     * @Column(name="get_about_time", type="integer")
     * @Required()
     */
    private $getAboutTime;

    /**
     * @var int $getOfferTime 获取最新报价条数的最后时间
     * @Column(name="get_offer_time", type="integer")
     * @Required()
     */
    private $getOfferTime;

    /**
     * @var int $lastTime 登录时间
     * @Column(name="last_time", type="integer", default=0)
     */
    private $lastTime;

    /**
     * @var int $alterTime 修改信息时间
     * @Column(name="alter_time", type="integer", default=0)
     */
    private $alterTime;

    /**
     * @var int $orderStatus 接单状态 0 不忙 1忙
     * @Column(name="order_status", type="integer", default=0)
     */
    private $orderStatus;

    /**
     * @var string $company 公司名称
     * @Column(name="company", type="string", length=150, default="''")
     */
    private $company;

    /**
     * @var int $companySize 公司规模 1 1至10人 2  10至100人 3 100人以上
     * @Column(name="company_size", type="integer", default=0)
     */
    private $companySize;

    /**
     * @var float $addressLatitude 地址维度
     * @Column(name="address_latitude", type="double", default=0)
     */
    private $addressLatitude;

    /**
     * @var float $addressLongitude 地址经度
     * @Column(name="address_longitude", type="double", default=0)
     */
    private $addressLongitude;

    /**
     * @var string $job 职位
     * @Column(name="job", type="string", length=100, default="''")
     */
    private $job;

    /**
     * @var string $contactName 联系人
     * @Column(name="contact_name", type="string", length=50, default="''")
     */
    private $contactName;

    /**
     * @var int $activity 用户活跃度
     * @Column(name="activity", type="integer", default=0)
     */
    private $activity;

    /**
     * @var int $isPush 是否接收aboutMe推送 0不接收 1接收
     * @Column(name="is_push", type="tinyint", default=1)
     */
    private $isPush;

    /**
     * @var int $phoneIsProtected 是否开启隐私保护 1:开启 0:关闭
     * @Column(name="phone_is_protected", type="tinyint", default=0)
     */
    private $phoneIsProtected;

    /**
     * @var int $qq qq
     * @Column(name="qq", type="integer", default=0)
     */
    private $qq;

    /**
     * @var string $mail email
     * @Column(name="mail", type="string", length=100, default="''")
     */
    private $mail;

    /**
     * @var int $level 用户等级 0:普通 1:铜牌 2:银牌 3:金牌 4:钻石
     * @Column(name="level", type="tinyint", default=0)
     */
    private $level;

    /**
     * @var int $identity 身份 采购商用 1设计工作室 2服装厂 3电商 4外贸企业 5 其它
     * @Column(name="identity", type="tinyint", default=0)
     */
    private $identity;

    /**
     * @var int $operationMode 供应商经营模式 1 生产商 2一级代理 3二级代理 4国际代理 5 其他
     * @Column(name="operation_mode", type="tinyint", default=0)
     */
    private $operationMode;

    /**
     * @var int $openNotification 系统通知权限 0:未同步 1:开启 2:关闭
     * @Column(name="open_notification", type="tinyint", default=0)
     */
    private $openNotification;

    /**
     * @var float $safePrice 保证金金额
     * @Column(name="safe_price", type="double", default=0)
     */
    private $safePrice;

    /**
     * @param int $value
     * @return $this
     */
    public function setUserId(int $value)
    {
        $this->userId = $value;

        return $this;
    }

    /**
     * 用户名称
     * @param string $value
     * @return $this
     */
    public function setName(string $value): self
    {
        $this->name = $value;

        return $this;
    }

    /**
     * 头像
     * @param string $value
     * @return $this
     */
    public function setPortrait(string $value): self
    {
        $this->portrait = $value;

        return $this;
    }

    /**
     * 背景图
     * @param string $value
     * @return $this
     */
    public function setBackground(string $value): self
    {
        $this->background = $value;

        return $this;
    }

    /**
     * 手机号码(登录名)
     * @param string $value
     * @return $this
     */
    public function setPhone(string $value): self
    {
        $this->phone = $value;

        return $this;
    }

    /**
     * 密码
     * @param string $value
     * @return $this
     */
    public function setPassword(string $value): self
    {
        $this->password = $value;

        return $this;
    }

    /**
     * 省份ID
     * @param int $value
     * @return $this
     */
    public function setProvinceId(int $value): self
    {
        $this->provinceId = $value;

        return $this;
    }

    /**
     * 省份
     * @param string $value
     * @return $this
     */
    public function setProvince(string $value): self
    {
        $this->province = $value;

        return $this;
    }

    /**
     * 城市ID
     * @param string $value
     * @return $this
     */
    public function setCityId(string $value): self
    {
        $this->cityId = $value;

        return $this;
    }

    /**
     * 城市
     * @param string $value
     * @return $this
     */
    public function setCity(string $value): self
    {
        $this->city = $value;

        return $this;
    }

    /**
     * 区ID,无效字段
     * @param int $value
     * @return $this
     */
    public function setAreaId(int $value): self
    {
        $this->areaId = $value;

        return $this;
    }

    /**
     * 区,无效字段
     * @param string $value
     * @return $this
     */
    public function setArea(string $value): self
    {
        $this->area = $value;

        return $this;
    }

    /**
     * 详细地址
     * @param string $value
     * @return $this
     */
    public function setDetailAddress(string $value): self
    {
        $this->detailAddress = $value;

        return $this;
    }

    /**
     * 主营产品
     * @param string $value
     * @return $this
     */
    public function setMainProduct(string $value): self
    {
        $this->mainProduct = $value;

        return $this;
    }

    /**
     * 与我相关推送标识id
     * @param string $value
     * @return $this
     */
    public function setCid(string $value): self
    {
        $this->cid = $value;

        return $this;
    }

    /**
     * 主营分词
     * @param string $value
     * @return $this
     */
    public function setMainProductKeyword(string $value): self
    {
        $this->mainProductKeyword = $value;

        return $this;
    }

    /**
     * 用户状态 1 正常 0 锁定
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * 用户锁定原因
     * @param string $value
     * @return $this
     */
    public function setLockedCause(string $value): self
    {
        $this->lockedCause = $value;

        return $this;
    }

    /**
     * 手机验证 0未验证 1已验证
     * @param int $value
     * @return $this
     */
    public function setVerifyPhone(int $value): self
    {
        $this->verifyPhone = $value;

        return $this;
    }

    /**
     * 手机类型 1安卓 2 IOS
     * @param int $value
     * @return $this
     */
    public function setPhoneType(int $value): self
    {
        $this->phoneType = $value;

        return $this;
    }

    /**
     * 是否是vip 0否 1是,无效字段
     * @param int $value
     * @return $this
     */
    public function setVip(int $value): self
    {
        $this->vip = $value;

        return $this;
    }

    /**
     * 是否是ka供应商 0否 1是,无效字段
     * @param int $value
     * @return $this
     */
    public function setKa(int $value): self
    {
        $this->ka = $value;

        return $this;
    }

    /**
     * 认证类型 0 未认证 1个人 2企业
     * @param int $value
     * @return $this
     */
    public function setCertificationType(int $value): self
    {
        $this->certificationType = $value;

        return $this;
    }

    /**
     * 认证采购商 0否 1是
     * @param int $value
     * @return $this
     */
    public function setPurchaser(int $value): self
    {
        $this->purchaser = $value;

        return $this;
    }

    /**
     * 认证供应商 0否 1是
     * @param int $value
     * @return $this
     */
    public function setSupplier(int $value): self
    {
        $this->supplier = $value;

        return $this;
    }

    /**
     * 店铺点击量
     * @param int $value
     * @return $this
     */
    public function setClicks(int $value): self
    {
        $this->clicks = $value;

        return $this;
    }

    /**
     * 发布求购量
     * @param int $value
     * @return $this
     */
    public function setBuyCount(int $value): self
    {
        $this->buyCount = $value;

        return $this;
    }

    /**
     * 发布产品量
     * @param int $value
     * @return $this
     */
    public function setProductCount(int $value): self
    {
        $this->productCount = $value;

        return $this;
    }

    /**
     * 微信ID
     * @param string $value
     * @return $this
     */
    public function setOpenid(string $value): self
    {
        $this->openid = $value;

        return $this;
    }

    /**
     * app登陆后openid
     * @param string $value
     * @return $this
     */
    public function setAppOpenid(string $value): self
    {
        $this->appOpenid = $value;

        return $this;
    }

    /**
     * 登陆次数
     * @param int $value
     * @return $this
     */
    public function setLoginTimes(int $value): self
    {
        $this->loginTimes = $value;

        return $this;
    }

    /**
     * 用户角色:1.采购商2.面料供应商3.辅料供应商4.加工/服务
     * @param int $value
     * @return $this
     */
    public function setRole(int $value): self
    {
        $this->role = $value;

        return $this;
    }

    /**
     * 主账号ID 如果没有主账号为0
     * @param int $value
     * @return $this
     */
    public function setParentId(int $value): self
    {
        $this->parentId = $value;

        return $this;
    }

    /**
     * 游客身份? 1:是 0:否
     * @param int $value
     * @return $this
     */
    public function setIsTourist(int $value): self
    {
        $this->isTourist = $value;

        return $this;
    }

    /**
     * 注册时间
     * @param int $value
     * @return $this
     */
    public function setRegTime(int $value): self
    {
        $this->regTime = $value;

        return $this;
    }

    /**
     * 设备号
     * @param string $value
     * @return $this
     */
    public function setDevice(string $value): self
    {
        $this->device = $value;

        return $this;
    }

    /**
     * 用户来源 0 公众号 1安卓 2ios 3安卓微信 4ios微信 5后台 6公众号注册后登陆app 7 安卓公众号注册后登陆app 8 安卓公众号注册后登陆app
     * @param int $value
     * @return $this
     */
    public function setFromType(int $value): self
    {
        $this->fromType = $value;

        return $this;
    }

    /**
     * 获取最新与我相关条数的最后时间
     * @param int $value
     * @return $this
     */
    public function setGetAboutTime(int $value): self
    {
        $this->getAboutTime = $value;

        return $this;
    }

    /**
     * 获取最新报价条数的最后时间
     * @param int $value
     * @return $this
     */
    public function setGetOfferTime(int $value): self
    {
        $this->getOfferTime = $value;

        return $this;
    }

    /**
     * 登录时间
     * @param int $value
     * @return $this
     */
    public function setLastTime(int $value): self
    {
        $this->lastTime = $value;

        return $this;
    }

    /**
     * 修改信息时间
     * @param int $value
     * @return $this
     */
    public function setAlterTime(int $value): self
    {
        $this->alterTime = $value;

        return $this;
    }

    /**
     * 接单状态 0 不忙 1忙
     * @param int $value
     * @return $this
     */
    public function setOrderStatus(int $value): self
    {
        $this->orderStatus = $value;

        return $this;
    }

    /**
     * 公司名称
     * @param string $value
     * @return $this
     */
    public function setCompany(string $value): self
    {
        $this->company = $value;

        return $this;
    }

    /**
     * 公司规模 1 1至10人 2  10至100人 3 100人以上
     * @param int $value
     * @return $this
     */
    public function setCompanySize(int $value): self
    {
        $this->companySize = $value;

        return $this;
    }

    /**
     * 地址维度
     * @param float $value
     * @return $this
     */
    public function setAddressLatitude(float $value): self
    {
        $this->addressLatitude = $value;

        return $this;
    }

    /**
     * 地址经度
     * @param float $value
     * @return $this
     */
    public function setAddressLongitude(float $value): self
    {
        $this->addressLongitude = $value;

        return $this;
    }

    /**
     * 职位
     * @param string $value
     * @return $this
     */
    public function setJob(string $value): self
    {
        $this->job = $value;

        return $this;
    }

    /**
     * 联系人
     * @param string $value
     * @return $this
     */
    public function setContactName(string $value): self
    {
        $this->contactName = $value;

        return $this;
    }

    /**
     * 用户活跃度
     * @param int $value
     * @return $this
     */
    public function setActivity(int $value): self
    {
        $this->activity = $value;

        return $this;
    }

    /**
     * 是否接收aboutMe推送 0不接收 1接收
     * @param int $value
     * @return $this
     */
    public function setIsPush(int $value): self
    {
        $this->isPush = $value;

        return $this;
    }

    /**
     * 是否开启隐私保护 1:开启 0:关闭
     * @param int $value
     * @return $this
     */
    public function setPhoneIsProtected(int $value): self
    {
        $this->phoneIsProtected = $value;

        return $this;
    }

    /**
     * qq
     * @param int $value
     * @return $this
     */
    public function setQq(int $value): self
    {
        $this->qq = $value;

        return $this;
    }

    /**
     * email
     * @param string $value
     * @return $this
     */
    public function setMail(string $value): self
    {
        $this->mail = $value;

        return $this;
    }

    /**
     * 用户等级 0:普通 1:铜牌 2:银牌 3:金牌 4:钻石
     * @param int $value
     * @return $this
     */
    public function setLevel(int $value): self
    {
        $this->level = $value;

        return $this;
    }

    /**
     * 身份 采购商用 1设计工作室 2服装厂 3电商 4外贸企业 5 其它
     * @param int $value
     * @return $this
     */
    public function setIdentity(int $value): self
    {
        $this->identity = $value;

        return $this;
    }

    /**
     * 供应商经营模式 1 生产商 2一级代理 3二级代理 4国际代理 5 其他
     * @param int $value
     * @return $this
     */
    public function setOperationMode(int $value): self
    {
        $this->operationMode = $value;

        return $this;
    }

    /**
     * 系统通知权限 0:未同步 1:开启 2:关闭
     * @param int $value
     * @return $this
     */
    public function setOpenNotification(int $value): self
    {
        $this->openNotification = $value;

        return $this;
    }

    /**
     * 保证金金额
     * @param float $value
     * @return $this
     */
    public function setSafePrice(float $value): self
    {
        $this->safePrice = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 用户名称
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 头像
     * @return mixed
     */
    public function getPortrait()
    {
        return $this->portrait;
    }

    /**
     * 背景图
     * @return mixed
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * 手机号码(登录名)
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * 密码
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * 省份ID
     * @return int
     */
    public function getProvinceId()
    {
        return $this->provinceId;
    }

    /**
     * 省份
     * @return mixed
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * 城市ID
     * @return mixed
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * 城市
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * 区ID,无效字段
     * @return int
     */
    public function getAreaId()
    {
        return $this->areaId;
    }

    /**
     * 区,无效字段
     * @return mixed
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * 详细地址
     * @return mixed
     */
    public function getDetailAddress()
    {
        return $this->detailAddress;
    }

    /**
     * 主营产品
     * @return mixed
     */
    public function getMainProduct()
    {
        return $this->mainProduct;
    }

    /**
     * 与我相关推送标识id
     * @return mixed
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * 主营分词
     * @return mixed
     */
    public function getMainProductKeyword()
    {
        return $this->mainProductKeyword;
    }

    /**
     * 用户状态 1 正常 0 锁定
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 用户锁定原因
     * @return mixed
     */
    public function getLockedCause()
    {
        return $this->lockedCause;
    }

    /**
     * 手机验证 0未验证 1已验证
     * @return int
     */
    public function getVerifyPhone()
    {
        return $this->verifyPhone;
    }

    /**
     * 手机类型 1安卓 2 IOS
     * @return int
     */
    public function getPhoneType()
    {
        return $this->phoneType;
    }

    /**
     * 是否是vip 0否 1是,无效字段
     * @return int
     */
    public function getVip()
    {
        return $this->vip;
    }

    /**
     * 是否是ka供应商 0否 1是,无效字段
     * @return int
     */
    public function getKa()
    {
        return $this->ka;
    }

    /**
     * 认证类型 0 未认证 1个人 2企业
     * @return int
     */
    public function getCertificationType()
    {
        return $this->certificationType;
    }

    /**
     * 认证采购商 0否 1是
     * @return int
     */
    public function getPurchaser()
    {
        return $this->purchaser;
    }

    /**
     * 认证供应商 0否 1是
     * @return int
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * 店铺点击量
     * @return int
     */
    public function getClicks()
    {
        return $this->clicks;
    }

    /**
     * 发布求购量
     * @return int
     */
    public function getBuyCount()
    {
        return $this->buyCount;
    }

    /**
     * 发布产品量
     * @return int
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    /**
     * 微信ID
     * @return mixed
     */
    public function getOpenid()
    {
        return $this->openid;
    }

    /**
     * app登陆后openid
     * @return mixed
     */
    public function getAppOpenid()
    {
        return $this->appOpenid;
    }

    /**
     * 登陆次数
     * @return int
     */
    public function getLoginTimes()
    {
        return $this->loginTimes;
    }

    /**
     * 用户角色:1.采购商2.面料供应商3.辅料供应商4.加工/服务
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * 主账号ID 如果没有主账号为0
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * 游客身份? 1:是 0:否
     * @return int
     */
    public function getIsTourist()
    {
        return $this->isTourist;
    }

    /**
     * 注册时间
     * @return int
     */
    public function getRegTime()
    {
        return $this->regTime;
    }

    /**
     * 设备号
     * @return mixed
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * 用户来源 0 公众号 1安卓 2ios 3安卓微信 4ios微信 5后台 6公众号注册后登陆app 7 安卓公众号注册后登陆app 8 安卓公众号注册后登陆app
     * @return int
     */
    public function getFromType()
    {
        return $this->fromType;
    }

    /**
     * 获取最新与我相关条数的最后时间
     * @return int
     */
    public function getGetAboutTime()
    {
        return $this->getAboutTime;
    }

    /**
     * 获取最新报价条数的最后时间
     * @return int
     */
    public function getGetOfferTime()
    {
        return $this->getOfferTime;
    }

    /**
     * 登录时间
     * @return int
     */
    public function getLastTime()
    {
        return $this->lastTime;
    }

    /**
     * 修改信息时间
     * @return int
     */
    public function getAlterTime()
    {
        return $this->alterTime;
    }

    /**
     * 接单状态 0 不忙 1忙
     * @return int
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * 公司名称
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * 公司规模 1 1至10人 2  10至100人 3 100人以上
     * @return int
     */
    public function getCompanySize()
    {
        return $this->companySize;
    }

    /**
     * 地址维度
     * @return float
     */
    public function getAddressLatitude()
    {
        return $this->addressLatitude;
    }

    /**
     * 地址经度
     * @return float
     */
    public function getAddressLongitude()
    {
        return $this->addressLongitude;
    }

    /**
     * 职位
     * @return mixed
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * 联系人
     * @return mixed
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * 用户活跃度
     * @return int
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * 是否接收aboutMe推送 0不接收 1接收
     * @return mixed
     */
    public function getIsPush()
    {
        return $this->isPush;
    }

    /**
     * 是否开启隐私保护 1:开启 0:关闭
     * @return int
     */
    public function getPhoneIsProtected()
    {
        return $this->phoneIsProtected;
    }

    /**
     * qq
     * @return int
     */
    public function getQq()
    {
        return $this->qq;
    }

    /**
     * email
     * @return mixed
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * 用户等级 0:普通 1:铜牌 2:银牌 3:金牌 4:钻石
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * 身份 采购商用 1设计工作室 2服装厂 3电商 4外贸企业 5 其它
     * @return int
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * 供应商经营模式 1 生产商 2一级代理 3二级代理 4国际代理 5 其他
     * @return int
     */
    public function getOperationMode()
    {
        return $this->operationMode;
    }

    /**
     * 系统通知权限 0:未同步 1:开启 2:关闭
     * @return int
     */
    public function getOpenNotification()
    {
        return $this->openNotification;
    }

    /**
     * 保证金金额
     * @return mixed
     */
    public function getSafePrice()
    {
        return $this->safePrice;
    }

}
