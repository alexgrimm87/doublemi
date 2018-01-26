<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CatalogPayment
 *
 * @ORM\Table(name="catalog_payment")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\CatalogPaymentRepository")
 */
class CatalogPayment extends Inheritance
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
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="service", type="string", length=255, nullable=true)
     */
    private $service;

    /**
     * @var boolean
     * @ORM\Column(name="uriInfo", type="boolean", nullable=true)
     */
    private $uriInfo;

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
     *
     * @return CatalogPayment
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
     * Set service
     *
     * @param string $service
     *
     * @return CatalogPayment
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return string
     */
    public function getService()
    {
        return $this->service;
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
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    public function isUriInfo()
    {
        return $this->uriInfo;
    }

    /**
     * @param boolean $uriInfo
     */
    public function setUriInfo($uriInfo)
    {
        $this->uriInfo = $uriInfo;
    }
}

