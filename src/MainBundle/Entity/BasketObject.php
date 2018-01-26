<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;


/**
 * BasketObject
 */
class BasketObject
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var integer
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "Минимальное количество для покупки 1."
     * )
     */
    private $count;

    /**
     * @var Product
     * @Assert\NotBlank()
     * @Serializer\Exclude()
     */
    private $product;

    /**
     * @var Package
     * @Serializer\Exclude()
     */
    private $package;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @Serializer\Exclude()
     */
    private $serv;

    /**
     * @var array
     */
    private $size;

    /**
     * @var array
     */
    private $data;

    public function __construct()
    {
        $this->serv = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param integer $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {

        $this->product = $product && $product->isEnabled() ? $product : null ;
    }

    /**
     * set package
     *
     * @param Package $package
     */
    public function setPackage(Package $package)
    {
        $this->package = $package && $package->isEnabled() ? $package : null ;
    }

    /**
     * Get package
     *
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Add serv
     *
     * @param Service $serv
     * @return BasketObject
     */
    public function addServ(Service $serv)
    {
        if($serv->isEnabled())
            $this->serv[] = $serv;
        return $this;
    }

    /**
     * Remove serv
     *
     * @param Service $serv
     * @return BasketObject
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
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("product")
     * @return int
     */
    public function getProductId()
    {
        return $this->product ? $this->product->getId() : false;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("package")
     * @return int
     */
    public function getPackageId()
    {
        return $this->package ? $this->package->getId() : false;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("serv")
     * @return array
     */
    public function getServId()
    {
        if(!$this->serv)
            return [];

        $arr = array_map(function($a){return $a->getId();},$this->serv->toArray());
        sort($arr);
        return $arr;
    }

    /**
     * @return array
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $title
     * @param integer $markup
     */
    public function setSize($title, $markup = 0)
    {
        $this->size = [
            'title'=>$title,
            'markup'=>intval($markup)
        ];
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function buildData(){
        $data = [];
        if($this->getProduct()) {
            $data['product'] = [
                'title' => $this->getProduct()->getTitle(),
                'markup' => $this->getProduct()->getPrice(),
            ];
        }

        if($this->getServ()){
            $data['serv'] = [];
            foreach ($this->getServ()->toArray() as $s){
                $data['serv'][] = [
                    'title' => $s->getTitle(),
                    'markup' => $s->getPrice(),
                ];
            }
        }

        if($this->getPackage()){
            $data['package'] = [
                'title' => $this->getPackage()->getTitle(),
                'markup' => 0
            ];
        }
        $this->data = $data;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("price")
     * @return int
     */
    public function getPrice(){
        $price = 0;
        if($this->product)
            $price += $this->product->getPrice();

        if($this->serv) {
            foreach ($this->serv->toArray() as $a) {
                $price += $a->getPrice();
            }
        }

        if($this->size && isset($this->size['markup']))
            $price += $this->size['markup'];

        return $price;
    }

    public function toArray(){
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        return $serializer->toArray($this);
    }

    static function fromArray($array, $controller){
        $object = new BasketObject();

        if(isset($array['product'])){
            $object->setProduct(
                $controller->get('doctrine')->getRepository('MainBundle:Product')->find(intval($array['product']))
            );
        }

        if(isset($array['serv']) && $array['serv']){
            if(!is_array($array['serv']))
                $array['serv'] = [$array['serv']];
            $service = $controller->get('doctrine')->getRepository('MainBundle:Service')->findBy(['id'=>array_map(function($a){return intval($a);},$array['serv'])]);
            if($service){
                foreach ($service as $s) {
                    $object->addServ($s);
                }
            }
        }

        if(isset($array['package']) && $array['package']){
            $package = $controller->get('doctrine')->getRepository('MainBundle:Package')->find(intval($array['package']));
            if($package){
                $object->setPackage($package);
            }
        }

        if(isset($array['count'])){
            $object->setCount(intval($array['count']));
        }

        if(isset($array['date'])){
            $object->setDate(new \DateTime($array['date']));
        }

        if(isset($array['size']) && isset($array['size']['title']) && isset($array['size']['markup'])){
            $object->setSize($array['size']['title'],$array['size']['markup']);
        }

        if(isset($array['data'])) {
            $object->setData($array['data']);
        }

        return $object;
    }
}

