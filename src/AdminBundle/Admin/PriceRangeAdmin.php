<?php
namespace AdminBundle\Admin;

use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use Doctrine\ORM\Mapping\Entity;
use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Color;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilder;
use AdminBundle\Page\BaseAdmin;

class PriceRangeAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){

        $builderForm->add('enabled', CheckboxType::class, ['required'=>false])
            ->add('code',TextType::class)
            ->add('title',TextType::class)
            ->add('minPrice',IntegerType::class)
            ->add('maxPrice',IntegerType::class,['required'=>false])
            ->add('pos',IntegerType::class,['required'=>false]);
    }

    public function getListFields(){
        return [
            [
                'name'=>'id'
            ],
            [
                'name'=>'title'
            ],
            [
                'name'=>'minPrice'
            ],
            [
                'name'=>'maxPrice'
            ],
            [
                'name'=>'enabled',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'pos'
            ]
        ];
    }

}