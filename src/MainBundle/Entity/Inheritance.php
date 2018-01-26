<?php
namespace MainBundle\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\EntityListeners;

/**
 * @MappedSuperclass
 */
abstract class Inheritance
{
    /**
     * @var int
     * @ORM\Column(name="pos", type="integer", nullable=true)
     */
    private $pos;

    /**
     * @var boolean
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * @var \DateTime
     * @ORM\Column(name="mod_date", type="datetime", nullable=true)
     */
    protected $modDate;

    public function __construct()
    {
        $this->pos = 0;
    }


    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Set modDate
     *
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setModDate($date)
    {
        $this->modDate = $date;

        return $this;
    }

    /**
     * Get modDate
     *
     * @return \DateTime
     */
    public function getModDate()
    {
        return $this->modDate;
    }

    /**
     * Set pos
     *
     * @param integer $pos
     *
     * @return $this
     */
    public function setPos($pos)
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get pos
     *
     * @return int
     */
    public function getPos()
    {
        return $this->pos;
    }

    function __toString()
    {
        return method_exists($this,'getTitle') ? $this->getTitle() : ($this->id ? strval($this->id) : '-') ;
    }
}