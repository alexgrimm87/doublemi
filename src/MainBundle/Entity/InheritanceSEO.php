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
abstract class InheritanceSEO extends Inheritance
{
    /**
     * @var string
     * @ORM\Column(name="meta_title", type="text", nullable=true)
     */
    private $metaTitle;

    /**
     * @var string
     * @ORM\Column(name="meta_description", type="text", nullable=true)
     */
    private $metaDescription;

    /**
     * @var string
     * @ORM\Column(name="meta_key_words", type="text", nullable=true)
     */
    private $metaKeyWords;

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param string $metaTitle
     *
     * @return $this
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaDescription
     *
     * @return $this
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKeyWords()
    {
        return $this->metaKeyWords;
    }

    /**
     * @param string $metaKeyWords
     *
     * @return $this
     */
    public function setMetaKeyWords($metaKeyWords)
    {
        $this->metaKeyWords = $metaKeyWords;

        return $this;
    }
}