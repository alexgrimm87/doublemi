<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Menu
 *
 * @ORM\Table(name="menu")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\MenuRepository")
 */
class Menu extends Inheritance
{
    static $cols = [
        1=>'footer.service',
        2=>'footer.clients',
        3=>'footer.conditions',
        4=>'footer.inform'
    ];

    const TYPE_CATALOG = 'catalog';
    const TYPE_GIFT = 'gist';
    const TYPE_WEDDING = 'wedding';

    static $types = [
        self::TYPE_CATALOG => 'Имеет подразделы "доставки цветов"',
        self::TYPE_GIFT => 'Имеет подразделы "подарков"',
        self::TYPE_WEDDING => 'Имеет подразделы "свадебной флористики"'
    ];

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
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=2048, nullable=true)
     */
    private $code;

    /**
     * @var boolean
     * @ORM\Column(name="top", type="boolean", nullable=true)
     */
    private $top;

    /**
     * @var boolean
     * @ORM\Column(name="main", type="boolean", nullable=true)
     */
    private $main;

    /**
     * @var boolean
     * @ORM\Column(name="footer", type="boolean", nullable=true)
     */
    private $footer;

    /**
     * @var boolean
     * @ORM\Column(name="mobile", type="boolean", nullable=true)
     */
    private $mobile;

    /**
     * @var integer
     * @ORM\Column(name="col", type="integer", nullable=true)
     */
    private $col;

    /**
     * @var string
     * @ORM\Column(name="url", type="string", length=4096, nullable=true)
     */
    private $url;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var Menu
     * @ORM\ManyToOne(targetEntity="\MainBundle\Entity\Menu")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent", referencedColumnName="id",onDelete="SET NULL")
     * })
     */
    private $parent;

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
     * Set title
     *
     * @param string $title
     * @return Menu
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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Menu
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isTop()
    {
        return $this->top;
    }

    /**
     * @param boolean $top
     *
     * @return $this
     */
    public function setTop($top)
    {
        $this->top = $top;

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
     * @return boolean
     */
    public function isFooter()
    {
        return $this->footer;
    }

    /**
     * @param boolean $footer
     *
     * @return $this
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isMobile()
    {
        return $this->mobile;
    }

    /**
     * @param boolean $mobile
     *
     * @return $this
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Set col
     *
     * @param integer $col
     *
     * @return $this
     */
    public function setCol($col)
    {
        $this->col = $col;

        return $this;
    }

    /**
     * Get col
     *
     * @return int
     */
    public function getCol()
    {
        return $this->col;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Menu
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return Menu
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Menu $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if(!$this->code && !$this->url){
            $context->buildViolation('В пункта меню должена быть указана страница или ссылка на нее')
                ->atPath('code')
                ->addViolation()
            ;
        }

        if ($this->footer && !$this->col){
            $context->buildViolation('Для пунктов меню в подвале сайта должна быть указана колонка')
                ->atPath('col')
                ->addViolation()
            ;
        }

        if(!$this->main && $this->parent){
            $context->buildViolation('Только пункты основного меню могут иметь родительский пункт')
                ->atPath('parent')
                ->addViolation()
            ;
        }
    }
}

