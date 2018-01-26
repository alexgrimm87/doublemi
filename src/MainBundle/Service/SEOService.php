<?php
namespace MainBundle\Service;


use MainBundle\Entity\SEO;

class SEOService
{
    /**
     * @var SEO
     */
    private $object;

    public function __construct()
    {
        $this->object = new SEO();
    }

    /**
     * @return SEO
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param SEO $object
     *
     * @return SEOService
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @param $object
     * @return $this
     */
    public function update($object){
        if(method_exists($object,'getMetaTitle') && $object->getMetaTitle()) {
            $this->object->setMetaTitle($object->getMetaTitle());
        }

        if(method_exists($object,'getMetaKeyWords') && $object->getMetaKeyWords())
            $this->object->setMetaKeyWords($object->getMetaKeyWords());

        if(method_exists($object,'getMetaDescription') && $object->getMetaDescription())
            $this->object->setMetaDescription($object->getMetaDescription());

        if(method_exists($object,'getPicture') && $object->getPicture())
            $this->object->setPicture($object->getPicture());

        if(!$this->getObject()->getMetaTitle() && method_exists($object,'getTitle')){
            $this->object->setMetaTitle($object->getTitle());
        }

        return $this;
    }

    /**
     * @param $picture
     * @return $this
     */
    public function updatePicture($picture){
        $this->object->setPicture($picture);

        return $this;
    }

    /**
     * @param $title
     * @return $this
     */
    public function updateTitle($title){
        $this->object->setMetaTitle($title);

        return $this;
    }
}