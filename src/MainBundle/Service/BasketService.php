<?php
namespace MainBundle\Service;



use MainBundle\Entity\Basket;
use MainBundle\Entity\BasketObject;
use MainBundle\Twig\MainExtension;
use Symfony\Component\Config\Definition\Exception\Exception;

class BasketService
{

    private $objectsList;
    private $needInit;
    /**
     * @var Basket
     */
    private $basket;

    public function __construct()
    {
        $this->objectsList = [];
        $this->needInit = true;
    }

    public function init(Basket $basket, $controller){
        $this->objectsList = [];
        if($basket->getObjects()){
            foreach ($basket->getObjects() as $key=>$row){
                $this->objectsList[$key] = BasketObject::fromArray($row,$controller);
            }
        }
        $this->basket = $basket;
        $this->needInit = false;
    }

    public function addObject(BasketObject $object){
        if($this->needInit)
            throw new \Exception('Need run "init" method of service before work with basket');

        $needAdd = true;
        if($this->objectsList){ // check for duplicates
            /**
             * @var BasketObject $value
             */
            foreach ($this->objectsList as $key=>$value){
                if(
                    $value->getProductId() == $object->getProductId()
                    &&
                    $value->getServId() == $object->getServId()
                    &&
                    $value->getSize() == $object->getSize()
                    &&
                    $value->getPackageId() == $object->getPackageId()
                ){
                    if($object->getDate())
                        $this->objectsList[$key]->setDate($object->getDate());

                    $count = $object->getCount() + $value->getCount();
                    $this->objectsList[$key]->setCount($count);
                    $needAdd = false;
                }
            }
        }

        if($needAdd){
            do {
                $key = rand(0, 99) . date('-H-i-s');
            } while (isset($this->objectsList[$key]));

            $this->objectsList[$key] = $object;
        }
    }

    public function removeObject($key){
        if($this->needInit)
            throw new \Exception('Need run "init" method of service before work with basket');

        if(!isset($this->objectsList[$key]))
            return false;

        unset($this->objectsList[$key]);
        return true;
    }

    public function changeCountObject($key, $count){
        if($this->needInit)
            throw new \Exception('Need run "init" method of service before work with basket');

        if(!isset($this->objectsList[$key]))
            return false;
        if($count>=1)
            $this->objectsList[$key]->setCount($count);
        return $this->objectsList[$key];
    }

    public function getBasketObjectList(){
        if($this->needInit)
            throw new \Exception('Need run "init" method of service before work with basket');

        return $this->objectsList;
    }

    public function getBasket(){
        if($this->needInit)
            throw new \Exception('Need run "init" method of service before work with basket');

        $array = [];
        if($this->objectsList){
            /**
             * @var BasketObject $value
             */
            foreach ($this->objectsList as $key=>$value){
                $array[$key] = $value->toArray();
            }
        }
        $this->basket->setObjects($array);
        return $this->basket;
    }

    public function buildDataObjects(){
        if($this->needInit)
            throw new \Exception('Need run "init" method of service before work with basket');

        if($this->objectsList){
            /**
             * @var BasketObject $value
             */
            foreach ($this->objectsList as $key=>$value){
                $this->objectsList[$key]->buildData();
            }
        }
    }

    public function validateObjects($validator){ //no one cat order invalid objects
        if($this->needInit)
            throw new \Exception('Need run "init" method of service before work with basket');

        if($this->objectsList){
            /**
             * @var BasketObject $value
             */
            foreach ($this->objectsList as $key=>$value){
                $errors = $validator->validate($value);
                if ($errors->count() != 0){
                    unset($this->objectsList[$key]);
                }
            }
        }
    }

    public function getInformationParams(){
        $count = 0;
        $price = 0;
        $spec = ['Товар', 'Товара', 'Товаров'];

        if($this->objectsList){
            /**
             * @var BasketObject $value
             */
            foreach ($this->objectsList as $key=>$value){
                $count += $value->getCount();
                $price += $value->getCount() * $value->getPrice();
            }
        }

        $str = self::plural($count,$spec);
        return [
            'count'=>$count,
            'str'=>$str,
            'price'=>$price,
            'price_format'=>MainExtension::priceFormatter($price)
        ];
    }

    public static function plural($number, $after) {
        $cases = array (2, 0, 1, 1, 1, 2);
        return $after[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
    }
}