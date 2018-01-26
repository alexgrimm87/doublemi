<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @UniqueEntity("code")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\ProductRepository")
 */
class Product extends InheritanceSEO
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^[\w\d_-]*$/",
     *     match=true,
     * )
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $text;

    /**
     * @var int
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "Не может принимать отрицательное значение"
     * )
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

    /**
     * @ORM\ManyToMany(targetEntity="Type")
     * @ORM\JoinTable(name="relation_product_type",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     *   }
     * )
     */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity="Target")
     * @ORM\JoinTable(name="relation_product_target",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="target_id", referencedColumnName="id")
     *   }
     * )
     */
    private $target;

    /**
     * @ORM\ManyToMany(targetEntity="Occasion")
     * @ORM\JoinTable(name="relation_product_occasion",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="occasion_id", referencedColumnName="id")
     *   }
     * )
     */
    private $occasion;

    /**
     * @ORM\ManyToMany(targetEntity="Note")
     * @ORM\JoinTable(name="relation_product_note",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="note_id", referencedColumnName="id")
     *   }
     * )
     */
    private $note;

    /**
     * @ORM\ManyToMany(targetEntity="Color")
     * @ORM\JoinTable(name="relation_product_color",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="color_id", referencedColumnName="id")
     *   }
     * )
     */
    private $color;

    /**
     * @ORM\ManyToMany(targetEntity="Season")
     * @ORM\JoinTable(name="relation_product_season",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="season_id", referencedColumnName="id")
     *   }
     * )
     */
    private $season;

    /**
     * @var CatalogSection
     * @ORM\ManyToOne(targetEntity="\MainBundle\Entity\CatalogSection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="catalog_section", referencedColumnName="id",onDelete="SET NULL")
     * })
     */
    private $catalogSection;

    /**
     * @ORM\ManyToMany(targetEntity="\MainBundle\Entity\Service")
     * @ORM\JoinTable(name="relation_product_service",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     *   }
     * )
     */
    private $serv;

    /**
     * @ORM\ManyToMany(targetEntity="\MainBundle\Entity\ProductFeatures")
     * @ORM\JoinTable(name="relation_product_features",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="feature_id", referencedColumnName="id")
     *   }
     * )
     */
    private $feature;

    /**
     * @ORM\ManyToMany(targetEntity="\MainBundle\Entity\ProductRelatedInfo")
     * @ORM\JoinTable(name="relation_product_related_info",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="related_info_id", referencedColumnName="id")
     *   }
     * )
     */
    private $relatedInfo;

    /**
     * @ORM\ManyToMany(targetEntity="\MainBundle\Entity\Package")
     * @ORM\JoinTable(name="relation_product_package",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="package_id", referencedColumnName="id")
     *   }
     * )
     */
    private $package;

    /**
     * @ORM\ManyToMany(targetEntity="\MainBundle\Entity\Product")
     * @ORM\JoinTable(name="relation_product_bonus_product",
     *     joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="bonus_product_id", referencedColumnName="id")
     *   }
     * )
     */
    private $bonusProduct;

    /**
     * @var string
     * @ORM\Column(name="picture", type="string", length=4096, nullable=true)
     */
    private $picture;

    /**
     * @var array
     * @ORM\Column(name="slider", type="array", nullable=true)
     */
    private $slider;

    /**
     * @var string
     *
     * @ORM\Column(name="compound", type="text", nullable=true)
     */
    private $compound;

    /**
     * @var boolean
     * @ORM\Column(name="moscow_free_delivery", type="boolean", nullable=true)
     */
    private $moscowFreeDelivery;

    /**
     * @var array
     * @ORM\Column(name="size", type="array", nullable=true)
     */
    private $size;

    public function __construct()
    {
        parent::__construct();
        $this->serv = new \Doctrine\Common\Collections\ArrayCollection();
        $this->relatedInfo = new \Doctrine\Common\Collections\ArrayCollection();
        $this->feature = new \Doctrine\Common\Collections\ArrayCollection();
        $this->package = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bonusProduct = new \Doctrine\Common\Collections\ArrayCollection();

        $this->type = new \Doctrine\Common\Collections\ArrayCollection();
        $this->target = new \Doctrine\Common\Collections\ArrayCollection();
        $this->occasion = new \Doctrine\Common\Collections\ArrayCollection();
        $this->note = new \Doctrine\Common\Collections\ArrayCollection();
        $this->color = new \Doctrine\Common\Collections\ArrayCollection();
        $this->season = new \Doctrine\Common\Collections\ArrayCollection();

    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Product
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Product
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Product
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }


    /**
     * @return CatalogSection
     */
    public function getCatalogSection()
    {
        return $this->catalogSection;
    }

    /**
     * @param CatalogSection $catalogSection
     *
     * @return $this
     */
    public function setCatalogSection($catalogSection)
    {
        $this->catalogSection = $catalogSection;

        return $this;
    }

    /**
     * Add serv
     *
     * @param Service $serv
     * @return Product
     */
    public function addServ(Service $serv)
    {
        $this->serv[] = $serv;
        return $this;
    }

    /**
     * Remove serv
     *
     * @param Service $serv
     * @return Product
     */
    public function removeServ(Service $serv)
    {
        $this->serv->removeElement($serv);
        return $this;
    }

    /**
     * Get serv
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServ()
    {
        return $this->serv;
    }

    /**
     * Add Feature
     *
     * @param ProductFeatures $feature
     * @return Product
     */
    public function addFeature(ProductFeatures $feature)
    {
        $this->feature[] = $feature;
        return $this;
    }

    /**
     * Remove Feature
     *
     * @param ProductFeatures $feature
     * @return Product
     */
    public function removeFeature(ProductFeatures $feature)
    {
        $this->feature->removeElement($feature);
        return $this;
    }

    /**
     * Get Feature
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Add package
     *
     * @param Package $package
     * @return Product
     */
    public function addPackage(Package $package)
    {
        $this->package[] = $package;
        return $this;
    }

    /**
     * Remove package
     *
     * @param Package $package
     * @return Product
     */
    public function removePackage(Package $package)
    {
        $this->package->removeElement($package);
        return $this;
    }

    /**
     * Get package
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Add bonusProduct
     *
     * @param Product $bonusProduct
     * @return Product
     */
    public function addBonusProduct(Product $bonusProduct)
    {
        $this->bonusProduct[] = $bonusProduct;
        return $this;
    }

    /**
     * Remove bonusProduct
     *
     * @param Product $bonusProduct
     * @return Product
     */
    public function removeBonusProduct(Product $bonusProduct)
    {
        $this->bonusProduct->removeElement($bonusProduct);
        return $this;
    }

    /**
     * Get bonusProduct
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBonusProduct()
    {
        return $this->bonusProduct;
    }

    /**
     * Add RelatedInfo
     *
     * @param ProductRelatedInfo $relatedInfo
     * @return Product
     */
    public function addRelatedInfo(ProductRelatedInfo $relatedInfo)
    {
        $this->relatedInfo[] = $relatedInfo;
        return $this;
    }

    /**
     * Remove RelatedInfo
     *
     * @param ProductRelatedInfo $relatedInfo
     * @return Product
     */
    public function removeRelatedInfo(ProductRelatedInfo $relatedInfo)
    {
        $this->relatedInfo->removeElement($relatedInfo);
        return $this;
    }

    /**
     * Get RelatedInfo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRelatedInfo()
    {
        return $this->relatedInfo;
    }

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     * @return Product
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Set compound
     *
     * @param string $compound
     * @return Product
     */
    public function setCompound($compound)
    {
        $this->compound = $compound;

        return $this;
    }

    /**
     * Get compound
     *
     * @return string
     */
    public function getCompound()
    {
        return $this->compound;
    }

    /**
     * @return boolean
     */
    public function isMoscowFreeDelivery()
    {
        return $this->moscowFreeDelivery;
    }

    /**
     * @param boolean $moscowFreeDelivery
     * @return Product
     */
    public function setMoscowFreeDelivery($moscowFreeDelivery)
    {
        $this->moscowFreeDelivery = $moscowFreeDelivery;

        return $this;
    }

    /**
     * Add type
     *
     * @param Type $type
     * @return Product
     */
    public function addType(Type $type)
    {
        $this->type[] = $type;
        return $this;
    }

    /**
     * Remove type
     *
     * @param Type $type
     * @return Product
     */
    public function removeType(Type $type)
    {
        $this->type->removeElement($type);
        return $this;
    }

    /**
     * Get type
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add target
     *
     * @param Target $target
     * @return Product
     */
    public function addTarget(Target $target)
    {
        $this->target[] = $target;
        return $this;
    }

    /**
     * Remove target
     *
     * @param Target $target
     * @return Product
     */
    public function removeTarget(Target $target)
    {
        $this->target->removeElement($target);
        return $this;
    }

    /**
     * Get target
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Add occasion
     *
     * @param Occasion $occasion
     * @return Product
     */
    public function addOccasion(Occasion $occasion)
    {
        $this->occasion[] = $occasion;
        return $this;
    }

    /**
     * Remove occasion
     *
     * @param Occasion $occasion
     * @return Product
     */
    public function removeOccasion(Occasion $occasion)
    {
        $this->occasion->removeElement($occasion);
        return $this;
    }

    /**
     * Get occasion
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOccasion()
    {
        return $this->occasion;
    }

    /**
     * Add note
     *
     * @param Note $note
     * @return Product
     */
    public function addNote(Note $note)
    {
        $this->note[] = $note;
        return $this;
    }

    /**
     * Remove note
     *
     * @param Note $note
     * @return Product
     */
    public function removeNote(Note $note)
    {
        $this->note->removeElement($note);
        return $this;
    }

    /**
     * Get note
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Add color
     *
     * @param Color $color
     * @return Product
     */
    public function addColor(Color $color)
    {
        $this->color[] = $color;
        return $this;
    }

    /**
     * Remove color
     *
     * @param Color $color
     * @return Product
     */
    public function removeColor(Color $color)
    {
        $this->color->removeElement($color);
        return $this;
    }

    /**
     * Get color
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Add season
     *
     * @param Season $season
     * @return Product
     */
    public function addSeason(Season $season)
    {
        $this->season[] = $season;
        return $this;
    }

    /**
     * Remove season
     *
     * @param Season $season
     * @return Product
     */
    public function removeSeason(Season $season)
    {
        $this->season->removeElement($season);
        return $this;
    }

    /**
     * Get season
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * Set slider
     *
     * @param array $slider
     *
     * @return Product
     */
    public function setSlider($slider)
    {
        $this->slider = $slider;

        return $this;
    }

    /**
     * Get slider
     *
     * @return array
     */
    public function getSlider()
    {
        return $this->slider;
    }

    /**
     * @return array
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param array $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    function isTotalEnabled(){
        if(!$this->isEnabled())
            return false;

        if(!$this->getCatalogSection())
            return false;

        if(!$this->getCatalogSection()->isEnabled())
            return false;

        if($this->getCatalogSection()->getParentSection()){
            if(!$this->getCatalogSection()->getParentSection()->isEnabled()){
                return false;
            }
        }

        return true;
    }
}
