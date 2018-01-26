<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 31.05.17
 * Time: 11:53
 */

namespace MainBundle\Admin;

use AdminBundle\Form\Type\ArrayObjectType;
use AdminBundle\Form\Type\Autocomplete2Type;
use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Form\Type\FileSliderType;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Color;
use MainBundle\Entity\Note;
use MainBundle\Entity\Occasion;
use MainBundle\Entity\Package;
use MainBundle\Entity\Portfolio;
use MainBundle\Entity\Product;
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
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use AdminBundle\Page\BaseAdmin;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\Request;
use AdminBundle\Form\Type\AutocompleteType;

class PortfolioAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){

        /**
         * @var Portfolio $obj
         */
        $obj = $this->getObject();
        if($obj->isEnabled()){
            $this->info->setLinkOnSite($this->controller->get('router')->generate('portfolio_element',['code'=>$obj->getCode()]));
        }

        $builderForm
            ->tab('base')
            ->add('enabled', CheckboxType::class, ['required'=>false])
            ->add('code', TextType::class)
            ->add('title',TextType::class)
            ->add('slider',FileSliderType::class)
            ->add('text',CKEditorType::class,[
                'required'=>false
            ])
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
                'name'=>'enabled',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'pos'
            ]
        ];
    }
}