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
 * 店铺产品表
 * @Entity()
 * @Table(name="sb_product")
 * @uses      Product
 */
class Product extends Model
{
    /**
     * @var int $proId 
     * @Id()
     * @Column(name="pro_id", type="integer")
     */
    private $proId;

    /**
     * @var int $userId 用户(店铺)ID
     * @Column(name="user_id", type="integer", default=0)
     */
    private $userId;

    /**
     * @var int $oldUserId 合并数据原归属ID
     * @Column(name="old_user_id", type="integer", default=0)
     */
    private $oldUserId;

    /**
     * @var int $cateId 分类id
     * @Column(name="cate_id", type="integer", default=0)
     */
    private $cateId;

    /**
     * @var string $cover 封面图片
     * @Column(name="cover", type="string", length=100, default="''")
     */
    private $cover;

    /**
     * @var string $realCover 新版面料封面图,已弃用
     * @Column(name="real_cover", type="string", length=200, default="''")
     */
    private $realCover;

    /**
     * @var int $imgWidth 封面图宽度,弃用字段
     * @Column(name="img_width", type="integer", default=0)
     */
    private $imgWidth;

    /**
     * @var int $imgHeight 封面图高度,弃用字段
     * @Column(name="img_height", type="integer", default=0)
     */
    private $imgHeight;

    /**
     * @var string $name 品名
     * @Column(name="name", type="string", length=100, default="''")
     */
    private $name;

    /**
     * @var string $proName 品名
     * @Column(name="pro_name", type="string", length=150, default="''")
     */
    private $proName;

    /**
     * @var string $uses 产品用途标签
     * @Column(name="uses", type="string", length=150, default="''")
     */
    private $uses;

    /**
     * @var string $ingredient 产品成分
     * @Column(name="ingredient", type="string", length=100, default="''")
     */
    private $ingredient;

    /**
     * @var string $pWidth 门幅
     * @Column(name="p_width", type="string", length=45, default="''")
     */
    private $pWidth;

    /**
     * @var string $price 价格
     * @Column(name="price", type="string", length=45, default="''")
     */
    private $price;

    /**
     * @var string $standard 规格
     * @Column(name="standard", type="string", length=50, default="''")
     */
    private $standard;

    /**
     * @var string $thickness 厚度
     * @Column(name="thickness", type="string", length=150, default="''")
     */
    private $thickness;

    /**
     * @var string $unit 单位
     * @Column(name="unit", type="string", length=45, default="''")
     */
    private $unit;

    /**
     * @var string $proItem 货号
     * @Column(name="pro_item", type="string", length=50, default="''")
     */
    private $proItem;

    /**
     * @var string $gramW 克重
     * @Column(name="gram_w", type="string", length=50, default="''")
     */
    private $gramW;

    /**
     * @var int $status 货源状态 0现货 1订做
     * @Column(name="status", type="tinyint", default=0)
     */
    private $status;

    /**
     * @var int $delStatus 产品状态 1正常 2删除 3:下架 4：管理员下架 5：待审核 6：审核失败
     * @Column(name="del_status", type="tinyint", default=1)
     */
    private $delStatus;

    /**
     * @var int $isRecommend 是否店铺推荐产品 0:否 1：是
     * @Column(name="is_recommend", type="tinyint", default=0)
     */
    private $isRecommend;

    /**
     * @var int $isUp 上架状态 1上架 0下架
     * @Column(name="is_up", type="tinyint", default=1)
     */
    private $isUp;

    /**
     * @var string $fabricDetail 面料详情
     * @Column(name="fabric_detail", type="string", length=255, default="''")
     */
    private $fabricDetail;

    /**
     * @var string $contacts 联系人,弃用字段
     * @Column(name="contacts", type="string", length=45, default="''")
     */
    private $contacts;

    /**
     * @var string $contactNum 联系电话,弃用字段
     * @Column(name="contact_num", type="string", length=20, default="''")
     */
    private $contactNum;

    /**
     * @var int $phoneIsPublic 联系方式是否公开,弃用字段
     * @Column(name="phone_is_public", type="tinyint", default=1)
     */
    private $phoneIsPublic;

    /**
     * @var string $proNum 产品编号 例:SP0024578954
     * @Column(name="pro_num", type="string", length=15, default="''")
     */
    private $proNum;

    /**
     * @var int $sendSample 是否寄样,弃用字段
     * @Column(name="send_sample", type="tinyint", default=0)
     */
    private $sendSample;

    /**
     * @var int $clicks 点击量
     * @Column(name="clicks", type="integer", default=0)
     */
    private $clicks;

    /**
     * @var int $fromType 发布来源 0 app 1微信
     * @Column(name="from_type", type="integer", default=0)
     */
    private $fromType;

    /**
     * @var int $addTime 提交时间
     * @Column(name="add_time", type="integer", default=0)
     */
    private $addTime;

    /**
     * @var int $type 商品类型 1:面料 2：辅料 3：坯布 5：针织类 6:梭织类 7：蕾丝/绣品 8：皮革/皮草 9：其他类面料
     * @Column(name="type", type="tinyint", default=1)
     */
    private $type;

    /**
     * @var int $alterTime 修改时间
     * @Column(name="alter_time", type="integer", default=0)
     */
    private $alterTime;

    /**
     * @var int $isAudit 审核状态 0 未审核 1审核通过 2审核未通过
     * @Column(name="is_audit", type="tinyint", default=0)
     */
    private $isAudit;

    /**
     * @var string $auditCase 审核失败原因
     * @Column(name="audit_case", type="string", length=100, default="''")
     */
    private $auditCase;

    /**
     * @var string $productTiledImages 产品平铺图
     * @Column(name="productTiledImages", type="string", length=500, default="''")
     */
    private $productTiledImages;

    /**
     * @var int $minOrderNum 起订量
     * @Column(name="minOrderNum", type="integer", default=0)
     */
    private $minOrderNum;

    /**
     * @var string $models 型号（纱支）
     * @Column(name="models", type="string", length=255, default="''")
     */
    private $models;

    /**
     * @var string $qualityStandards 品质标准
     * @Column(name="qualityStandards", type="string", length=255)
     * @Required()
     */
    private $qualityStandards;

    /**
     * @var string $density 密度
     * @Column(name="density", type="string", length=255, default="''")
     */
    private $density;

    /**
     * @var string $gramWeightUnit 克重单位
     * @Column(name="gramWeightUnit", type="string", length=255)
     * @Required()
     */
    private $gramWeightUnit;

    /**
     * @var int $validTime 新版面料有效期,弃用字段
     * @Column(name="valid_time", type="integer", default=0)
     */
    private $validTime;

    /**
     * @var int $refreshTime 刷新时间
     * @Column(name="refresh_time", type="integer", default=0)
     */
    private $refreshTime;

    /**
     * @var int $refreshCount 刷新次数
     * @Column(name="refresh_count", type="integer", default=0)
     */
    private $refreshCount;

    /**
     * @var string $labelKey 标签关键词
     * @Column(name="label_key", type="string", length=100, default="''")
     */
    private $labelKey;

    /**
     * @var float $cutPrice 剪样价格
     * @Column(name="cut_price", type="float", default=0)
     */
    private $cutPrice;

    /**
     * @var string $cutUnits 剪样单位
     * @Column(name="cut_units", type="string", length=45, default="''")
     */
    private $cutUnits;

    /**
     * @var float $cutWeight 剪样单位重量
     * @Column(name="cut_weight", type="float", default=0)
     */
    private $cutWeight;

    /**
     * @var float $goodsWeight 大货单位重量
     * @Column(name="goods_weight", type="float", default=0)
     */
    private $goodsWeight;

    /**
     * @var string $season 季节
     * @Column(name="season", type="string", length=50, default="''")
     */
    private $season;

    /**
     * @var string $color 颜色
     * @Column(name="color", type="string", length=255, default="''")
     */
    private $color;

    /**
     * @var string $flower 花型
     * @Column(name="flower", type="string", length=100, default="''")
     */
    private $flower;

    /**
     * @var float $cardPrice 色卡价格
     * @Column(name="card_price", type="float", default=0)
     */
    private $cardPrice;

    /**
     * @var int $cardShipType 色卡运费类型:0 运费到付 1:包邮 2:默认未选中
     * @Column(name="card_ship_type", type="tinyint", default=0)
     */
    private $cardShipType;

    /**
     * @var string $crafts 工艺
     * @Column(name="crafts", type="string", length=255, default="''")
     */
    private $crafts;

    /**
     * @var string $material 材质
     * @Column(name="material", type="string", length=150, default="''")
     */
    private $material;

    /**
     * @var int $mid 管理员编号(审核人)
     * @Column(name="mid", type="integer", default=0)
     */
    private $mid;

    /**
     * @var string $failCause 失败原因
     * @Column(name="fail_cause", type="string", length=45, default="NULL")
     */
    private $failCause;

    /**
     * @param int $value
     * @return $this
     */
    public function setProId(int $value)
    {
        $this->proId = $value;

        return $this;
    }

    /**
     * 用户(店铺)ID
     * @param int $value
     * @return $this
     */
    public function setUserId(int $value): self
    {
        $this->userId = $value;

        return $this;
    }

    /**
     * 合并数据原归属ID
     * @param int $value
     * @return $this
     */
    public function setOldUserId(int $value): self
    {
        $this->oldUserId = $value;

        return $this;
    }

    /**
     * 分类id
     * @param int $value
     * @return $this
     */
    public function setCateId(int $value): self
    {
        $this->cateId = $value;

        return $this;
    }

    /**
     * 封面图片
     * @param string $value
     * @return $this
     */
    public function setCover(string $value): self
    {
        $this->cover = $value;

        return $this;
    }

    /**
     * 新版面料封面图,已弃用
     * @param string $value
     * @return $this
     */
    public function setRealCover(string $value): self
    {
        $this->realCover = $value;

        return $this;
    }

    /**
     * 封面图宽度,弃用字段
     * @param int $value
     * @return $this
     */
    public function setImgWidth(int $value): self
    {
        $this->imgWidth = $value;

        return $this;
    }

    /**
     * 封面图高度,弃用字段
     * @param int $value
     * @return $this
     */
    public function setImgHeight(int $value): self
    {
        $this->imgHeight = $value;

        return $this;
    }

    /**
     * 品名
     * @param string $value
     * @return $this
     */
    public function setName(string $value): self
    {
        $this->name = $value;

        return $this;
    }

    /**
     * 品名
     * @param string $value
     * @return $this
     */
    public function setProName(string $value): self
    {
        $this->proName = $value;

        return $this;
    }

    /**
     * 产品用途标签
     * @param string $value
     * @return $this
     */
    public function setUses(string $value): self
    {
        $this->uses = $value;

        return $this;
    }

    /**
     * 产品成分
     * @param string $value
     * @return $this
     */
    public function setIngredient(string $value): self
    {
        $this->ingredient = $value;

        return $this;
    }

    /**
     * 门幅
     * @param string $value
     * @return $this
     */
    public function setPWidth(string $value): self
    {
        $this->pWidth = $value;

        return $this;
    }

    /**
     * 价格
     * @param string $value
     * @return $this
     */
    public function setPrice(string $value): self
    {
        $this->price = $value;

        return $this;
    }

    /**
     * 规格
     * @param string $value
     * @return $this
     */
    public function setStandard(string $value): self
    {
        $this->standard = $value;

        return $this;
    }

    /**
     * 厚度
     * @param string $value
     * @return $this
     */
    public function setThickness(string $value): self
    {
        $this->thickness = $value;

        return $this;
    }

    /**
     * 单位
     * @param string $value
     * @return $this
     */
    public function setUnit(string $value): self
    {
        $this->unit = $value;

        return $this;
    }

    /**
     * 货号
     * @param string $value
     * @return $this
     */
    public function setProItem(string $value): self
    {
        $this->proItem = $value;

        return $this;
    }

    /**
     * 克重
     * @param string $value
     * @return $this
     */
    public function setGramW(string $value): self
    {
        $this->gramW = $value;

        return $this;
    }

    /**
     * 货源状态 0现货 1订做
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

        return $this;
    }

    /**
     * 产品状态 1正常 2删除 3:下架 4：管理员下架 5：待审核 6：审核失败
     * @param int $value
     * @return $this
     */
    public function setDelStatus(int $value): self
    {
        $this->delStatus = $value;

        return $this;
    }

    /**
     * 是否店铺推荐产品 0:否 1：是
     * @param int $value
     * @return $this
     */
    public function setIsRecommend(int $value): self
    {
        $this->isRecommend = $value;

        return $this;
    }

    /**
     * 上架状态 1上架 0下架
     * @param int $value
     * @return $this
     */
    public function setIsUp(int $value): self
    {
        $this->isUp = $value;

        return $this;
    }

    /**
     * 面料详情
     * @param string $value
     * @return $this
     */
    public function setFabricDetail(string $value): self
    {
        $this->fabricDetail = $value;

        return $this;
    }

    /**
     * 联系人,弃用字段
     * @param string $value
     * @return $this
     */
    public function setContacts(string $value): self
    {
        $this->contacts = $value;

        return $this;
    }

    /**
     * 联系电话,弃用字段
     * @param string $value
     * @return $this
     */
    public function setContactNum(string $value): self
    {
        $this->contactNum = $value;

        return $this;
    }

    /**
     * 联系方式是否公开,弃用字段
     * @param int $value
     * @return $this
     */
    public function setPhoneIsPublic(int $value): self
    {
        $this->phoneIsPublic = $value;

        return $this;
    }

    /**
     * 产品编号 例:SP0024578954
     * @param string $value
     * @return $this
     */
    public function setProNum(string $value): self
    {
        $this->proNum = $value;

        return $this;
    }

    /**
     * 是否寄样,弃用字段
     * @param int $value
     * @return $this
     */
    public function setSendSample(int $value): self
    {
        $this->sendSample = $value;

        return $this;
    }

    /**
     * 点击量
     * @param int $value
     * @return $this
     */
    public function setClicks(int $value): self
    {
        $this->clicks = $value;

        return $this;
    }

    /**
     * 发布来源 0 app 1微信
     * @param int $value
     * @return $this
     */
    public function setFromType(int $value): self
    {
        $this->fromType = $value;

        return $this;
    }

    /**
     * 提交时间
     * @param int $value
     * @return $this
     */
    public function setAddTime(int $value): self
    {
        $this->addTime = $value;

        return $this;
    }

    /**
     * 商品类型 1:面料 2：辅料 3：坯布 5：针织类 6:梭织类 7：蕾丝/绣品 8：皮革/皮草 9：其他类面料
     * @param int $value
     * @return $this
     */
    public function setType(int $value): self
    {
        $this->type = $value;

        return $this;
    }

    /**
     * 修改时间
     * @param int $value
     * @return $this
     */
    public function setAlterTime(int $value): self
    {
        $this->alterTime = $value;

        return $this;
    }

    /**
     * 审核状态 0 未审核 1审核通过 2审核未通过
     * @param int $value
     * @return $this
     */
    public function setIsAudit(int $value): self
    {
        $this->isAudit = $value;

        return $this;
    }

    /**
     * 审核失败原因
     * @param string $value
     * @return $this
     */
    public function setAuditCase(string $value): self
    {
        $this->auditCase = $value;

        return $this;
    }

    /**
     * 产品平铺图
     * @param string $value
     * @return $this
     */
    public function setProductTiledImages(string $value): self
    {
        $this->productTiledImages = $value;

        return $this;
    }

    /**
     * 起订量
     * @param int $value
     * @return $this
     */
    public function setMinOrderNum(int $value): self
    {
        $this->minOrderNum = $value;

        return $this;
    }

    /**
     * 型号（纱支）
     * @param string $value
     * @return $this
     */
    public function setModels(string $value): self
    {
        $this->models = $value;

        return $this;
    }

    /**
     * 品质标准
     * @param string $value
     * @return $this
     */
    public function setQualityStandards(string $value): self
    {
        $this->qualityStandards = $value;

        return $this;
    }

    /**
     * 密度
     * @param string $value
     * @return $this
     */
    public function setDensity(string $value): self
    {
        $this->density = $value;

        return $this;
    }

    /**
     * 克重单位
     * @param string $value
     * @return $this
     */
    public function setGramWeightUnit(string $value): self
    {
        $this->gramWeightUnit = $value;

        return $this;
    }

    /**
     * 新版面料有效期,弃用字段
     * @param int $value
     * @return $this
     */
    public function setValidTime(int $value): self
    {
        $this->validTime = $value;

        return $this;
    }

    /**
     * 刷新时间
     * @param int $value
     * @return $this
     */
    public function setRefreshTime(int $value): self
    {
        $this->refreshTime = $value;

        return $this;
    }

    /**
     * 刷新次数
     * @param int $value
     * @return $this
     */
    public function setRefreshCount(int $value): self
    {
        $this->refreshCount = $value;

        return $this;
    }

    /**
     * 标签关键词
     * @param string $value
     * @return $this
     */
    public function setLabelKey(string $value): self
    {
        $this->labelKey = $value;

        return $this;
    }

    /**
     * 剪样价格
     * @param float $value
     * @return $this
     */
    public function setCutPrice(float $value): self
    {
        $this->cutPrice = $value;

        return $this;
    }

    /**
     * 剪样单位
     * @param string $value
     * @return $this
     */
    public function setCutUnits(string $value): self
    {
        $this->cutUnits = $value;

        return $this;
    }

    /**
     * 剪样单位重量
     * @param float $value
     * @return $this
     */
    public function setCutWeight(float $value): self
    {
        $this->cutWeight = $value;

        return $this;
    }

    /**
     * 大货单位重量
     * @param float $value
     * @return $this
     */
    public function setGoodsWeight(float $value): self
    {
        $this->goodsWeight = $value;

        return $this;
    }

    /**
     * 季节
     * @param string $value
     * @return $this
     */
    public function setSeason(string $value): self
    {
        $this->season = $value;

        return $this;
    }

    /**
     * 颜色
     * @param string $value
     * @return $this
     */
    public function setColor(string $value): self
    {
        $this->color = $value;

        return $this;
    }

    /**
     * 花型
     * @param string $value
     * @return $this
     */
    public function setFlower(string $value): self
    {
        $this->flower = $value;

        return $this;
    }

    /**
     * 色卡价格
     * @param float $value
     * @return $this
     */
    public function setCardPrice(float $value): self
    {
        $this->cardPrice = $value;

        return $this;
    }

    /**
     * 色卡运费类型:0 运费到付 1:包邮 2:默认未选中
     * @param int $value
     * @return $this
     */
    public function setCardShipType(int $value): self
    {
        $this->cardShipType = $value;

        return $this;
    }

    /**
     * 工艺
     * @param string $value
     * @return $this
     */
    public function setCrafts(string $value): self
    {
        $this->crafts = $value;

        return $this;
    }

    /**
     * 材质
     * @param string $value
     * @return $this
     */
    public function setMaterial(string $value): self
    {
        $this->material = $value;

        return $this;
    }

    /**
     * 管理员编号(审核人)
     * @param int $value
     * @return $this
     */
    public function setMid(int $value): self
    {
        $this->mid = $value;

        return $this;
    }

    /**
     * 失败原因
     * @param string $value
     * @return $this
     */
    public function setFailCause(string $value): self
    {
        $this->failCause = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProId()
    {
        return $this->proId;
    }

    /**
     * 用户(店铺)ID
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 合并数据原归属ID
     * @return int
     */
    public function getOldUserId()
    {
        return $this->oldUserId;
    }

    /**
     * 分类id
     * @return int
     */
    public function getCateId()
    {
        return $this->cateId;
    }

    /**
     * 封面图片
     * @return mixed
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * 新版面料封面图,已弃用
     * @return mixed
     */
    public function getRealCover()
    {
        return $this->realCover;
    }

    /**
     * 封面图宽度,弃用字段
     * @return int
     */
    public function getImgWidth()
    {
        return $this->imgWidth;
    }

    /**
     * 封面图高度,弃用字段
     * @return int
     */
    public function getImgHeight()
    {
        return $this->imgHeight;
    }

    /**
     * 品名
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 品名
     * @return mixed
     */
    public function getProName()
    {
        return $this->proName;
    }

    /**
     * 产品用途标签
     * @return mixed
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * 产品成分
     * @return mixed
     */
    public function getIngredient()
    {
        return $this->ingredient;
    }

    /**
     * 门幅
     * @return mixed
     */
    public function getPWidth()
    {
        return $this->pWidth;
    }

    /**
     * 价格
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * 规格
     * @return mixed
     */
    public function getStandard()
    {
        return $this->standard;
    }

    /**
     * 厚度
     * @return mixed
     */
    public function getThickness()
    {
        return $this->thickness;
    }

    /**
     * 单位
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * 货号
     * @return mixed
     */
    public function getProItem()
    {
        return $this->proItem;
    }

    /**
     * 克重
     * @return mixed
     */
    public function getGramW()
    {
        return $this->gramW;
    }

    /**
     * 货源状态 0现货 1订做
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 产品状态 1正常 2删除 3:下架 4：管理员下架 5：待审核 6：审核失败
     * @return mixed
     */
    public function getDelStatus()
    {
        return $this->delStatus;
    }

    /**
     * 是否店铺推荐产品 0:否 1：是
     * @return int
     */
    public function getIsRecommend()
    {
        return $this->isRecommend;
    }

    /**
     * 上架状态 1上架 0下架
     * @return mixed
     */
    public function getIsUp()
    {
        return $this->isUp;
    }

    /**
     * 面料详情
     * @return mixed
     */
    public function getFabricDetail()
    {
        return $this->fabricDetail;
    }

    /**
     * 联系人,弃用字段
     * @return mixed
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * 联系电话,弃用字段
     * @return mixed
     */
    public function getContactNum()
    {
        return $this->contactNum;
    }

    /**
     * 联系方式是否公开,弃用字段
     * @return mixed
     */
    public function getPhoneIsPublic()
    {
        return $this->phoneIsPublic;
    }

    /**
     * 产品编号 例:SP0024578954
     * @return mixed
     */
    public function getProNum()
    {
        return $this->proNum;
    }

    /**
     * 是否寄样,弃用字段
     * @return int
     */
    public function getSendSample()
    {
        return $this->sendSample;
    }

    /**
     * 点击量
     * @return int
     */
    public function getClicks()
    {
        return $this->clicks;
    }

    /**
     * 发布来源 0 app 1微信
     * @return int
     */
    public function getFromType()
    {
        return $this->fromType;
    }

    /**
     * 提交时间
     * @return int
     */
    public function getAddTime()
    {
        return $this->addTime;
    }

    /**
     * 商品类型 1:面料 2：辅料 3：坯布 5：针织类 6:梭织类 7：蕾丝/绣品 8：皮革/皮草 9：其他类面料
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 修改时间
     * @return int
     */
    public function getAlterTime()
    {
        return $this->alterTime;
    }

    /**
     * 审核状态 0 未审核 1审核通过 2审核未通过
     * @return int
     */
    public function getIsAudit()
    {
        return $this->isAudit;
    }

    /**
     * 审核失败原因
     * @return mixed
     */
    public function getAuditCase()
    {
        return $this->auditCase;
    }

    /**
     * 产品平铺图
     * @return mixed
     */
    public function getProductTiledImages()
    {
        return $this->productTiledImages;
    }

    /**
     * 起订量
     * @return int
     */
    public function getMinOrderNum()
    {
        return $this->minOrderNum;
    }

    /**
     * 型号（纱支）
     * @return mixed
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * 品质标准
     * @return string
     */
    public function getQualityStandards()
    {
        return $this->qualityStandards;
    }

    /**
     * 密度
     * @return mixed
     */
    public function getDensity()
    {
        return $this->density;
    }

    /**
     * 克重单位
     * @return string
     */
    public function getGramWeightUnit()
    {
        return $this->gramWeightUnit;
    }

    /**
     * 新版面料有效期,弃用字段
     * @return int
     */
    public function getValidTime()
    {
        return $this->validTime;
    }

    /**
     * 刷新时间
     * @return int
     */
    public function getRefreshTime()
    {
        return $this->refreshTime;
    }

    /**
     * 刷新次数
     * @return int
     */
    public function getRefreshCount()
    {
        return $this->refreshCount;
    }

    /**
     * 标签关键词
     * @return mixed
     */
    public function getLabelKey()
    {
        return $this->labelKey;
    }

    /**
     * 剪样价格
     * @return float
     */
    public function getCutPrice()
    {
        return $this->cutPrice;
    }

    /**
     * 剪样单位
     * @return mixed
     */
    public function getCutUnits()
    {
        return $this->cutUnits;
    }

    /**
     * 剪样单位重量
     * @return float
     */
    public function getCutWeight()
    {
        return $this->cutWeight;
    }

    /**
     * 大货单位重量
     * @return float
     */
    public function getGoodsWeight()
    {
        return $this->goodsWeight;
    }

    /**
     * 季节
     * @return mixed
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * 颜色
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * 花型
     * @return mixed
     */
    public function getFlower()
    {
        return $this->flower;
    }

    /**
     * 色卡价格
     * @return float
     */
    public function getCardPrice()
    {
        return $this->cardPrice;
    }

    /**
     * 色卡运费类型:0 运费到付 1:包邮 2:默认未选中
     * @return int
     */
    public function getCardShipType()
    {
        return $this->cardShipType;
    }

    /**
     * 工艺
     * @return mixed
     */
    public function getCrafts()
    {
        return $this->crafts;
    }

    /**
     * 材质
     * @return mixed
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * 管理员编号(审核人)
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * 失败原因
     * @return mixed
     */
    public function getFailCause()
    {
        return $this->failCause;
    }

}
