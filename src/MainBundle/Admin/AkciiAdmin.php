<?php
namespace MainBundle\Admin;

use AdminBundle\Form\Type\Autocomplete2Type;
use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use MainBundle\Entity\Akcii;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use AdminBundle\Page\BaseAdmin;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;

class AkciiAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){
        /**
         * @var Akcii $obj
         */
        $obj = $this->getObject();
        if($obj && $obj->isEnabled()){
            $this->info->setLinkOnSite($this->controller->get('router')->generate('akcii_element',['code'=>$obj->getCode()]));
        }

        $builderForm
            ->tab('base')
                ->add('enabled', CheckboxType::class, ['required'=>false])
                ->add('code', TextType::class)
                ->add('title',TextType::class)
                ->add('price',IntegerType::class, ['required'=>false])
                ->add('picture',FileselectType::class)
                ->add('text',CKEditorType::class,[
                    'required'=>false
                ])
                ->add('similarAkcii',Autocomplete2Type::class,[
                    'class'=>'MainBundle:Akcii',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_AKCII_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
                ->add('pos',IntegerType::class,['required'=>false])
            ->tab('main_page')
                ->add('onMain', CheckboxType::class, ['required'=>false])
                ->add('mainTitle',TextType::class)
                ->add('mainSubTitle',TextType::class)
                ->add('mainDiscount',TextType::class)
                ->add('mainPicture',FileselectType::class)
            ;

        $this->addSeoBlock($builderForm);
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
                'name'=>'enabled',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'onMain',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'pos'
            ]
        ];
    }
}