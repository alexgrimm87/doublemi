<?php
namespace MainBundle\Admin;

use AdminBundle\Page\BaseAdmin;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;

class InfoAdmin extends BaseAdmin
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
                'name'=>'title'
            ],
            [
                'name'=>'code'
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

        if($this->object && $this->object->getId() && $this->object->isEnabled()){
            $this->info->setLinkOnSite($this->controller->get('router')->generate('info_element',['code'=>$this->object->getCode()]));
        }

        $builderForm
            ->tab('base')
            ->add('enabled', CheckboxType::class, ['required'=>false])
            ->add('code', TextType::class)
            ->add('title',TextType::class)

            ->add('text',CKEditorType::class,[
                'required'=>false
            ])
            ->add('pos',IntegerType::class,['required'=>false]);

        $this->addSeoBlock($builderForm);
    }
}