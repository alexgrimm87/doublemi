<?php
namespace AdminBundle\Admin;

use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Color;
use MainBundle\Entity\Note;
use MainBundle\Entity\Occasion;
use MainBundle\Entity\Package;
use MainBundle\Entity\ProductFeatures;
use MainBundle\Entity\ProductRelatedInfo;
use MainBundle\Entity\Season;
use MainBundle\Entity\Service;
use MainBundle\Entity\Target;
use MainBundle\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use AdminBundle\Page\BaseAdmin;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;

class ProductAdmin extends BaseAdmin
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
                        ->add('code', TextType::class)
                        ->add('title',TextType::class)
                        ->add('catalogSection',EntityType::class,['class'=>CatalogSection::class])
                        ->add('price',IntegerType::class)
                        ->add('picture',FileselectType::class)
                    ->tab('params')
                        ->add('moscowFreeDelivery', CheckboxType::class,['required'=>false])
                        ->add('type',EntityType::class,['class'=>Type::class, 'required'=>false])
                        ->add('target',EntityType::class,['class'=>Target::class, 'required'=>false])
                        ->add('occasion',EntityType::class,['class'=>Occasion::class, 'required'=>false])
                        ->add('note',EntityType::class,['class'=>Note::class, 'required'=>false])
                        ->add('color',EntityType::class,['class'=>Color::class, 'required'=>false])
                        ->add('season',EntityType::class,['class'=>Season::class, 'required'=>false])
                        ->add('service',EntityType::class,['class'=>Service::class, 'required'=>false, 'multiple' => true, 'expanded' => true])
                        ->add('compound',TextareaType::class,['required'=>false])
                        ->add('text',CKEditorType::class,[
                            'required'=>false
                        ])
                        ->add('relatedInfo',EntityType::class,['class'=>ProductRelatedInfo::class, 'required'=>false, 'multiple' => true, 'expanded' => true])
                        ->add('features',EntityType::class,['class'=>ProductFeatures::class, 'required'=>false, 'multiple' => true, 'expanded' => true])
                        ->add('package',EntityType::class,['class'=>Package::class, 'required'=>false, 'multiple' => true, 'expanded' => true])
                    ->tab('base')
                        ->add('pos',IntegerType::class,['required'=>false]);

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
                'name'=>'pos'
            ]
        ];
    }
}