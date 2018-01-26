<?php
namespace MainBundle\Admin;

use AdminBundle\Form\Type\Autocomplete2Type;
use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use AdminBundle\Page\BaseAdmin;
use MainBundle\Entity\Menu;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Serializer\Exception\UnsupportedException;

class MenuAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){

        $codes = $this->controller->getParameter('menu');

        //find parent array

        $array = $this->controller->get('doctrine')->getRepository('MainBundle:Menu')->findBy(['main'=>true, 'enabled'=>true ],['pos'=>'ASC']);
        if($array){
            /**
             * @var Menu $v
             */
            $obj = $this->getObject();
            foreach ($array as $k=>$v){
                if($v->getParent()){
                    unset($array[$k]);
                    continue;
                }

                if($obj && $obj->getId() == $v->getId()){
                    unset($array[$k]);
                    continue;
                }
            }
        }

        $builderForm
            ->tab('base')
            ->add('enabled', CheckboxType::class, ['required'=>false])
            ->add('mobile', CheckboxType::class, [
                'required'=>false,
                'label'=>'menu.mobile'
            ])
            ->add('top', CheckboxType::class, [
                'required'=>false,
                'label'=>'menu.top'
            ])
            ->add('main', CheckboxType::class, [
                'required'=>false,
                'label'=>'menu.main'
            ])
            ->add('footer', CheckboxType::class, [
                'required'=>false,
                'label'=>'menu.footer'
            ])
            ->add('code', ChoiceType::class,[
                'label'=>'Page',
                'choices'=>$codes,
                'required'=>false
            ])
            ->add('url',UrlType::class,['required'=>false])
            ->add('type', ChoiceType::class,[
                'label'=>'Второй уровень',
                'choices'=>Menu::$types,
                'required'=>false
            ])
            ->add('title',TextType::class)
            ->add('col',ChoiceType::class, [
                'choices'=>Menu::$cols,
                'required'=>false
            ]);

        if($array) {
            $builderForm
                ->add('parent', EntityType::class, [
                    'class' => 'MainBundle:Menu',
                    'empty_data'=>null,
                    'choices' => $array,
                    'required'=>false
                ]);
        }
        $builderForm
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
                'name'=>'mobile',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'top',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'main',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'footer',
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