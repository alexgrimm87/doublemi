<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MainPageFigure
 *
 * @ORM\Table(name="main_page_figure")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\MainPageFigureRepository")
 */
class MainPageFigure extends Inheritance
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
     * @ORM\Column(name="in_delivery", type="boolean", nullable=true)
     */
    private $inDelivery;

    /**
     * @var boolean
     * @ORM\Column(name="in_present", type="boolean", nullable=true)
     */
    private $inPresent;

    /**
     * @var boolean
     * @ORM\Column(name="in_wedding", type="boolean", nullable=true)
     */
    private $inWedding;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="price", type="string", length=255, nullable=true)
     */
    private $price;

    /**
     * @var string
     * @ORM\Column(name="picture", type="string", length=2048, nullable=true)
     */
    private $picture;

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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return MainPageFigure
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return MainPageFigure
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $price
     * @return MainPageFigure
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
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
     * @return MainPageFigure
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isInDelivery()
    {
        return $this->inDelivery;
    }

    /**
     * @param boolean $inDelivery
     */
    public function setInDelivery($inDelivery)
    {
        $this->inDelivery = $inDelivery;
    }

    /**
     * @return boolean
     */
    public function isInPresent()
    {
        return $this->inPresent;
    }

    /**
     * @param boolean $inPresent
     */
    public function setInPresent($inPresent)
    {
        $this->inPresent = $inPresent;
    }

    /**
     * @return boolean
     */
    public function isInWedding()
    {
        return $this->inWedding;
    }

    /**
     * @param boolean $inWedding
     */
    public function setInWedding($inWedding)
    {
        $this->inWedding = $inWedding;
    }
}

