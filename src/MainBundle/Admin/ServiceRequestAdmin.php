<?php
namespace MainBundle\Admin;

use AdminBundle\Page\FormHolder;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use AdminBundle\Page\BaseAdmin;

class ServiceRequestAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'date'=>'DESC'
        ];
    }

    public function __construct($entity)
    {
        parent::__construct($entity);

        $this->setActionList([
            BaseAdmin::ACTION_DELETE,
            BaseAdmin::ACTION_EDIT,
            BaseAdmin::ACTION_LIST,
        ]);
    }


    public function getFormFields(FormHolder &$builderForm){

        $builderForm
            ->tab('base')
            ->add('name',TextType::class)
            ->add('email',EmailType::class)
            ->add('phone',TextType::class)
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
                'name'=>'phone'
            ],
            [
                'name'=>'email'
            ],
            [
                'name'=>'date',
                'format'=>'H:i d/m/Y'
            ]
        ];
    }
}