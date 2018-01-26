<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * CatalogSection
 *
 * @ORM\Table(name="catalog_section")
 * @UniqueEntity("code")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\CatalogSectionRepository")
 */
class CatalogSection extends InheritanceSEO
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var CatalogSection
     * @ORM\ManyToOne(targetEntity="\MainBundle\Entity\CatalogSection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_section", referencedColumnName="id",onDelete="CASCADE")
     * })
     */
    private $parentSection;

    /**
     * @var boolean
     * @ORM\Column(name="main", type="boolean", nullable=true)
     */
    private $main;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $text;

    /**
     * @var boolean
     * @ORM\Column(name="in_catalog_menu", type="boolean", nullable=true)
     */
    private $inCatalogMenu;

    /**
     * @var boolean
     * @ORM\Column(name="in_gift_menu", type="boolean", nullable=true)
     */
    private $inGiftMenu;

    /**
     * @var boolean
     * @ORM\Column(name="in_wedding_menu", type="boolean", nullable=true)
     */
    private $inWeddingMenu;


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
     * @return CatalogSection
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
     * @return CatalogSection
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
     * @return CatalogSection
     */
    public function getParentSection()
    {
        return $this->parentSection;
    }

    /**
     * @param CatalogSection $parentSection
     *
     * @return $this
     */
    public function setParentSection($parentSection)
    {
        $this->parentSection = $parentSection;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isMain()
    {
        return $this->main;
    }

    /**
     * @param boolean $main
     *
     * @return $this
     */
    public function setMain($main)
    {
        $this->main = $main;

        return $this;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return CatalogSection
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
     * @return boolean
     */
    public function isInCatalogMenu()
    {
        return $this->inCatalogMenu;
    }

    /**
     * @param boolean $inCatalogMenu
     */
    public function setInCatalogMenu($inCatalogMenu)
    {
        $this->inCatalogMenu = $inCatalogMenu;
    }

    /**
     * @return boolean
     */
    public function isInGiftMenu()
    {
        return $this->inGiftMenu;
    }

    /**
     * @param boolean $inGiftMenu
     */
    public function setInGiftMenu($inGiftMenu)
    {
        $this->inGiftMenu = $inGiftMenu;
    }

    /**
     * @return boolean
     */
    public function isInWeddingMenu()
    {
        return $this->inWeddingMenu;
    }

    /**
     * @param boolean $inWeddingMenu
     */
    public function setInWeddingMenu($inWeddingMenu)
    {
        $this->inWeddingMenu = $inWeddingMenu;
    }


    function __toString()
    {
        return $this->title ? $this->title : ($this->id ? strval($this->id) : '-') ;
    }

    function isTotalEnabled(){
        if(!$this->isEnabled())
            return false;

        if($this->getParentSection()){
            if(!$this->getParentSection()->isEnabled())
                return false;

            if($this->getParentSection()->getParentSection()){
                if(!$this->getParentSection()->getParentSection()->isEnabled()){
                    return false;
                }
            }
        }

        return true;
    }
}
