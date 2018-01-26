<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Akcii
 *
 * @ORM\Table(name="akcii")
 * @UniqueEntity("code")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\AkciiRepository")
 */
class Akcii extends InheritanceSEO
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
     * @var boolean
     * @ORM\Column(name="on_main", type="boolean", nullable=true)
     */
    private $onMain;

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
     *
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
     * @var string
     * @ORM\Column(name="picture", type="string", length=2048, nullable=true)
     */
    private $picture;

    /**
     * @ORM\ManyToMany(targetEntity="Akcii")
     * @ORM\JoinTable(name="relation_akcii_similar_akcii",
     *     joinColumns={
     *     @ORM\JoinColumn(name="akcii_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="similar_akcii_id", referencedColumnName="id")
     *   }
     * )
     */
    private $similarAkcii;

    /**
     * @var string
     *
     * @ORM\Column(name="main_title", type="string", length=2048, nullable=true)
     */
    private $mainTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="main_sub_title", type="string", length=2048, nullable=true)
     */
    private $mainSubTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="main_discount", type="string", length=2048, nullable=true)
     */
    private $mainDiscount;

    /**
     * @var string
     * @ORM\Column(name="main_picture", type="string", length=2048, nullable=true)
     */
    private $mainPicture;

    public function __construct()
    {
        parent::__construct();
        $this->similarAkcii = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set code
     *
     * @param string $code
     * @return Akcii
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
     * @return Akcii
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
     * @return Akcii
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
     * @return Akcii
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
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     * @return Akcii
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }


    /**
     * Add similarAkcii
     *
     * @param Akcii $similarAkcii
     * @return Akcii
     */
    public function addSimilarAkcius(Akcii $similarAkcii)
    {
        $this->similarAkcii[] = $similarAkcii;
        return $this;
    }

    /**
     * Remove similarAkcii
     *
     * @param Akcii $similarAkcii
     * @return Akcii
     */
    public function removeSimilarAkcius(Akcii $similarAkcii)
    {
        $this->similarAkcii->removeElement($similarAkcii);
        return $this;
    }

    /**
     * Get similarAkcii
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSimilarAkcii()
    {
        return $this->similarAkcii;
    }

    /**
     * @return boolean
     */
    public function isOnMain()
    {
        return $this->onMain;
    }

    /**
     * @param boolean $onMain
     */
    public function setOnMain($onMain)
    {
        $this->onMain = $onMain;
    }

    /**
     * @return string
     */
    public function getMainTitle()
    {
        return $this->mainTitle;
    }

    /**
     * @param string $mainTitle
     */
    public function setMainTitle($mainTitle)
    {
        $this->mainTitle = $mainTitle;
    }

    /**
     * @return string
     */
    public function getMainSubTitle()
    {
        return $this->mainSubTitle;
    }

    /**
     * @param string $mainSubTitle
     */
    public function setMainSubTitle($mainSubTitle)
    {
        $this->mainSubTitle = $mainSubTitle;
    }

    /**
     * @return string
     */
    public function getMainDiscount()
    {
        return $this->mainDiscount;
    }

    /**
     * @param string $mainDiscount
     */
    public function setMainDiscount($mainDiscount)
    {
        $this->mainDiscount = $mainDiscount;
    }

    /**
     * @return string
     */
    public function getMainPicture()
    {
        return $this->mainPicture;
    }

    /**
     * @param string $picture
     * @return Akcii
     */
    public function setMainPicture($picture)
    {
        $this->mainPicture = $picture;

        return $this;
    }
}

