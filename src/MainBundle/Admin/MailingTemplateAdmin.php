<?php
namespace MainBundle\Admin;

use AdminBundle\Form\Type\Autocomplete2Type;
use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use AdminBundle\Page\BaseAdmin;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\Request;
use AdminBundle\Form\Type\AutocompleteType;

class MailingTemplateAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'date'=>'DESC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){

        $builderForm
            ->tab('base')
                ->add('date', DateTimeType::class,['label'=>'Время россылки'])
                ->add('generated', CheckboxType::class,['label'=>'Рассылка проведена', 'required'=>false])
                ->add('name', TextType::class)
                ->add('fromName',TextType::class,['label'=>'Имя отправителя'])
                ->add('subject',TextType::class)
                ->add('text',CKEditorType::class)
        ;
    }

    public function getListFields(){
        return [
            [
                'name'=>'id'
            ],
            [
                'name'=>'name'
            ],
            [
                'name'=>'generated',
                'label'=>'Рассылка проведена',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'date',
                'label'=>'Время россылки'
            ]
        ];
    }
}