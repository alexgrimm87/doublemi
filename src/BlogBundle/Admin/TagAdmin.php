<?php
namespace BlogBundle\Admin;

use AdminBundle\Page\BaseAdmin;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TagAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    public function getListFields(){
        return [
            [
                'name'=>'id'
            ],
            [
                'name'=>'text'
            ],
            [
                'name'=>'code'
            ],
            [
                'name'=>'search',
                'label'=>'In Search Block'
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

    public function getFormFields(FormHolder &$builderForm){

        $builderForm
            ->tab('base')
                ->add('enabled', CheckboxType::class, ['required'=>false])
                ->add('search', CheckboxType::class, ['required'=>false, 'label'=>'In Search Block'])
                ->add('code', TextType::class)
                ->add('text', TextType::class)
                ->add('pos', IntegerType::class)
        ;
    }
}