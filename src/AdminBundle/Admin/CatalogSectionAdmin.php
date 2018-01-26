<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 03.05.17
 * Time: 10:00
 */

namespace AdminBundle\Admin;


use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use Doctrine\ORM\Mapping\Entity;
use MainBundle\Entity\CatalogSection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilder;
use AdminBundle\Page\BaseAdmin;

class CatalogSectionAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){
        /**
         * @var CatalogSection $obj
         */
        $obj = $this->getObject();

        $builderForm->add('enabled', CheckboxType::class, ['required'=>false])
                    ->add('main', CheckboxType::class, ['required'=>false])
                    ->add('code', TextType::class)
                    ->add('title',TextType::class);

        if($obj){
            if($obj->isMain()){
                $choices = [];
                $objects = $this->controller->getDoctrine()->getRepository($this->getRepositoryNameFromEntityName())->findBy(['main'=>1,'parentSection'=>null],$this->getListOrder());
                if($objects){
                    //normalize
                    foreach ($objects as $element){
                        if($obj->getId() != $element->getId()) {
                            $choices[] = $element;
                        }
                    }
                    if($choices)
                        $builderForm->add('parentSection', EntityType::class, ['class'=>CatalogSection::class,'required'=>false, 'choices'=>$choices]);
                }
            }
        }
        $builderForm->add('pos',IntegerType::class,['required'=>false]);
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
                'name'=>'enabled',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'pos'
            ]
        ];
    }
}