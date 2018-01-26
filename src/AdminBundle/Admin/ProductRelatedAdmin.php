<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 06.05.17
 * Time: 16:34
 */

namespace AdminBundle\Admin;


use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use Doctrine\ORM\Mapping\Entity;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Color;
use MainBundle\Entity\Note;
use MainBundle\Entity\Occasion;
use MainBundle\Entity\Season;
use MainBundle\Entity\Service;
use MainBundle\Entity\Target;
use MainBundle\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilder;
use AdminBundle\Page\BaseAdmin;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProductRelatedAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){

        $builderForm->add('enabled', CheckboxType::class, ['required'=>false])
            ->add('title',TextType::class)
            ->add('description',TextareaType::class)
            ->add('picture',FileselectType::class)
        ;

        if(method_exists($this->object,'getLink')){
            $builderForm->add('link',UrlType::class,['required'=>false]);
        }

        if(method_exists($this->object,'getText')){
            $builderForm->add('text',CKEditorType::class,['required'=>false]);
        }

        $builderForm->add('pos',IntegerType::class,['required'=>false]);
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