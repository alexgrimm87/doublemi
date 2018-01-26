<?php
namespace MainBundle\Admin;

use AdminBundle\Form\Type\Autocomplete2Type;
use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use MainBundle\Entity\Akcii;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use AdminBundle\Page\BaseAdmin;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;


class MainPageFigureAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){
        $builderForm
            ->tab('base')
            ->add('enabled', CheckboxType::class, ['required'=>false])
            ->add('inDelivery', CheckboxType::class, ['required'=>false])
            ->add('inPresent', CheckboxType::class, ['required'=>false])
            ->add('inWedding', CheckboxType::class, ['required'=>false])
            ->add('title',TextType::class)
            ->add('price',TextType::class,['required'=>false])
            ->add('picture',FileselectType::class)
            ->add('description',TextareaType::class,[
                'required'=>false
            ])
            ->add('pos',IntegerType::class,['required'=>false])
        ;
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
                'name'=>'price'
            ],
            [
                'name'=>'inDelivery',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'inPresent',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'inWedding',
                'callback'=>CallbackGenerator::getBooleanCallback()
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