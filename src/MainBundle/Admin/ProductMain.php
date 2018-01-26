<?php

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

class ProductMain extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    protected function getListFilter(Request $request, $offset=0){
        $filter['_sort'] = $this->getListOrder();
        $filter = $request->query->all();
        $filter['_offset'] = $offset;
        $filter['_limit'] = $this->countOnPage;
        $filter[$this->filterKey]['catalogSection']['main'] = 1;

        return $filter;
    }

    public function getFormFields(FormHolder &$builderForm){

        /**
         * @var Product $obj
         */
        $obj = $this->getObject();
        if($obj->isTotalEnabled()){
            $this->info->setLinkOnSite($this->controller->get('router')->generate('catalog_main_section_product',['section_code'=>$obj->getCatalogSection()->getCode(),'product_code'=>$obj->getCode()]));
        }

        $builderForm
            ->tab('base')
                ->add('enabled', CheckboxType::class, ['required'=>false])
                ->add('code', TextType::class)
                ->add('title',TextType::class)
                ->add('catalogSection',EntityType::class,[
                    'class'=>CatalogSection::class,
                    'query_builder' => function ($er) {
                        return $er->createQueryBuilder('c')
                            ->where('c.main = 1')
                            ->orderBy('c.pos');
                    }
                ])
                ->add('price',IntegerType::class)
                ->add('size',ArrayObjectType::class,[
                    'required'=>false,
                    'label'=>'Sizes',
                    'fields'=>[
                        ArrayObjectType::addField('title','text','Размер'),
                        ArrayObjectType::addField('markup','number','Наценка','',false),
                    ]
                ])
                ->add('picture',FileselectType::class)
                ->add('bonusProduct',Autocomplete2Type::class,[
                    'class'=>'MainBundle:Product',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_PRODUCTUNMAIN_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
            ->tab('params')
                ->add('moscowFreeDelivery', CheckboxType::class,['required'=>false])
                ->add('type',Autocomplete2Type::class,[
                    'class'=>'MainBundle:Type',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_TYPE_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
                ->add('target',Autocomplete2Type::class,[
                    'class'=>'MainBundle:Target',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_TARGET_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
                ->add('occasion',Autocomplete2Type::class,[
                    'class'=>'MainBundle:Occasion',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_OCCASION_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
                ->add('note',Autocomplete2Type::class,[
                    'class'=>'MainBundle:Note',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_NOTE_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
                ->add('color',Autocomplete2Type::class,[
                    'class'=>'MainBundle:Color',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_COLOR_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
                ->add('season',Autocomplete2Type::class,[
                    'class'=>'MainBundle:Season',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_SEASON_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
                ->add('compound',TextareaType::class,['required'=>false])
                ->add('slider',FileSliderType::class)
                ->add('text',CKEditorType::class,[
                    'required'=>false
                ])

                ->add('serv',Autocomplete2Type::class,[
                    'class'=>'MainBundle:Service',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_SERVICE_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
                ->add('relatedInfo',Autocomplete2Type::class,[
                    'class'=>'MainBundle:ProductRelatedInfo',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_RELATED_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
                ->add('feature',Autocomplete2Type::class,[
                    'class'=>'MainBundle:ProductFeatures',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_FEATURES_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
                ->add('package',Autocomplete2Type::class,[
                    'class'=>'MainBundle:Package',
                    'multiple'=>true,
                    'property_url' => 'ROUTE_MAIN_CATALOG_PACKAGE_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'title',
                    'required' => false
                ])
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