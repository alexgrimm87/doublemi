<?php

namespace BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @UniqueEntity("code")
 * @ORM\Entity(repositoryClass="BlogBundle\Repository\TagRepository")
 */
class Tag
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
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * @var boolean
     * @ORM\Column(name="search", type="boolean", nullable=true)
     */
    private $search;

    /**
     * @var int
     * @ORM\Column(name="pos", type="integer", nullable=true)
     */
    private $pos;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^[\w\d_-]*$/",
     *     match=true,
     * )
     * @ORM\Column(name="code", type="string", length=128)
     */
    private $code;

    /**
     * Tag text
     *
     * @var string $text
     * @Assert\NotBlank()
     * @ORM\Column(name="text", type="string", length=255)
     */
    private $text = '';

    /**
     * @var Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="BlogBundle\Entity\Post", mappedBy="tags")
     */
    private $posts;

    public function __construct($text = null)
    {
        $this->text = $text;
        $this->enabled = true;
        $this->posts = new ArrayCollection();
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
     * Set Tag text
     *
     * @param string $text A tag text
     *
     * @return void
     */
    public function setText($text)
    {
        $this->text = $text;
    }
    /**
     * Get Tag text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get posts for this tag
     *
     * @return ArrayCollection
     */
    public function getPosts()
    {
        return $this->posts;
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
     * @return boolean
     */
    public function isSearch()
    {
        return $this->search;
    }

    /**
     * @param boolean $search
     *
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
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

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * This method allows a class to decide how it will react when it is treated like a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getText()?:'-';
    }
}

