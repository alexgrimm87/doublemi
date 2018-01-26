<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 25.05.17
 * Time: 10:28
 */

namespace MainBundle\Admin;

use AdminBundle\Form\Type\Autocomplete2Type;
use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use AdminBundle\Page\BaseAdmin;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SEOAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){

        $codes = $this->controller->getParameter('menu');

        $builderForm
            ->tab('base')
            ->add('enabled', CheckboxType::class, ['required'=>false])
            ->add('code', ChoiceType::class,['label'=>'Page', 'choices'=>$codes])
            ->add('title',TextType::class)
            ->add('picture',FileselectType::class)
            ->add('metaTitle',TextType::class,['required'=>false])
            ->add('metaDescription',TextareaType::class,['required'=>false])
            ->add('metaKeyWords',TextareaType::class,['required'=>false])
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
                'name'=>'enabled',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'pos'
            ]
        ];
    }
}