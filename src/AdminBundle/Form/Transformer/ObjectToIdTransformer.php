<?php

namespace AdminBundle\Form\Transformer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ObjectToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @var string
     */
    private $class;

    /**
     * @var boolean
     */
    private $multiple;
    /**
     * @param ManagerRegistry $registry
     * @param string          $class
     * @param boolean          $multiple
     */
    public function __construct(ManagerRegistry $registry, $class, $multiple = false)
    {
        $this->registry = $registry;
        $this->class = $class;
        $this->multiple = $multiple;
    }
    /**
     * Transforms an object (object) to a string (id).
     *
     * @param object|null $object
     *
     * @return string
     */
    public function transform($object)
    {
        if (null === $object) {
            return [];
        }
        $resp = [
            '_labels'=>[]
        ];
        if(method_exists($object,'getId')) {
            $resp[] = $object->getId();
            $resp['_labels'][] = strval($object);
        }else{
            foreach ($object->toArray() as $a){
                $resp[] = $a->getId();
                $resp['_labels'][] = strval($a);
            };
        }
        return $resp;
    }
    /**
     * Transforms a string (id) to an object (object).
     *
     * @param string $id
     *
     * @throws TransformationFailedException if object (object) is not found
     *
     * @return object|array|null
     */
    public function reverseTransform($id)
    {
        if (empty($id)) {
            return $this->multiple?[]:null;
        }

        if($this->multiple){
            $objects = $this->registry->getManagerForClass($this->class)->getRepository($this->class)->findById($id);
            if($objects){
                $collection = new \Doctrine\Common\Collections\ArrayCollection();
                foreach ($objects as $object){
                    $collection->add($object);
                }
                return $collection;
            }
        } else {

            $object = $this->registry->getManagerForClass($this->class)->getRepository($this->class)->find($id);
            if (null === $object) {
                throw new TransformationFailedException(sprintf('Object from class %s with id "%s" not found', $this->class, $id));
            }
            return $object;
        }
    }
}