<?php
namespace MainBundle\Admin;

use AdminBundle\Form\Type\Autocomplete2Type;
use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Page\FormHolder;
use JMS\Serializer\Tests\Fixtures\Order;
use MainBundle\Controller\BaseController;
use MainBundle\Entity\Basket;
use MainBundle\Entity\CatalogPayment;
use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Color;
use MainBundle\Entity\Note;
use MainBundle\Entity\Occasion;
use MainBundle\Entity\OrderRequest;
use MainBundle\Entity\Package;
use MainBundle\Entity\Product;
use MainBundle\Entity\ProductFeatures;
use MainBundle\Entity\ProductRelatedInfo;
use MainBundle\Entity\Season;
use MainBundle\Entity\Service;
use MainBundle\Entity\Target;
use MainBundle\Entity\Type;
use MainBundle\Twig\MainExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use AdminBundle\Page\BaseAdmin;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\Request;
use AdminBundle\Form\Type\AutocompleteType;

class OrderRequestAdmin extends BaseAdmin
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

        /**
         * @var OrderRequest $obj
         */
        $obj = $this->getObject();
        if($obj){
            $service = $this->controller->get('main.basket');
            $basket = new Basket();
            $basket->setObjects($obj->getObjects());
            $service->init($basket, $this->controller);
            $objectList = $service->getBasketObjectList();

            $this->info->addContentFooterElements('Состав заказа',$this->renderView('MainBundle:Catalog:orderObjectsList.html.twig', ['order' => $obj, 'list' => $objectList]));
        }

        $builderForm
            ->tab('base')
                ->add('oneClickOrder',CheckboxType::class,['required'=>false])
                ->add('status',ChoiceType::class,['choices'=>OrderRequest::$status_list])
                ->add('name',TextType::class)
                ->add('phone',TextType::class)
                ->add('email', EmailType::class, ['required'=>false])
                ->add('user', Autocomplete2Type::class,[
                    'class'=>'UserBundle:User',
                    'multiple'=>false,
                    'property_url' => 'ROUTE_APP_USERLIST_LIST',
                    'property_url_params'=>[],
                    'property_field'=>'username',
                    'required' => false
                ])
                ->add('selfReceive', CheckboxType::class, ['required'=>false])
                ->add('incognito', CheckboxType::class, ['required'=>false])
                ->add('freeCard', CheckboxType::class, ['required'=>false])
                ->add('cardText', TextareaType::class, ['required'=>false])
                ->add('cardSign', TextType::class, ['required'=>false])
                ->add('delivery',ChoiceType::class,['choices'=>OrderRequest::$delivery_list])
                ->add('callForAddress', CheckboxType::class, ['required'=>false])
                ->add('deliveryDate', DateTimeType::class)
                ->add('deliveryCity', TextType::class, ['required'=>false])
                ->add('deliveryName', TextType::class, ['required'=>false])
                ->add('deliveryPhone', TextType::class, ['required'=>false])
                ->add('deliveryStreet', TextType::class, ['required'=>false])
                ->add('deliveryPrice', IntegerType::class)
                ->add('comment', TextareaType::class, ['required'=>false])
                ->add('catalogPayment', EntityType::class, ['class'=>CatalogPayment::class, 'required'=>false])
                ->add('summ',IntegerType::class)
            ->tab('org_info')
                ->add('orgName', TextType::class, ['required'=>false])
                ->add('uriAddress', TextType::class, ['required'=>false])
                ->add('factAddress', TextType::class, ['required'=>false])
                ->add('uriPhone', TextType::class, ['required'=>false])
                ->add('inn', TextType::class, ['required'=>false])
            ->tab('bank_info')
                ->add('bankName', TextType::class, ['required'=>false])
                ->add('bankCheckingIndex', TextType::class, ['required'=>false])
                ->add('bankCorrespondentIndex', TextType::class, ['required'=>false])
                ->add('bankIndex', TextType::class, ['required'=>false])
        ;
    }

    public function getListFields(){
        $list = OrderRequest::$status_list;
        return [
            [
                'name'=>'id'
            ],
            [
                'name'=>'status',
                'callback'=>function($a) use ($list) {
                    return isset($list[$a]) ? $list[$a] : ' - ';
                }
            ],
            [
                'name'=>'summ',
                'callback'=>function($a){
                    return MainExtension::priceFormatter($a);
                }
            ],
            [
                'name'=>'date',
                'format'=>'H:i d/m/Y'
            ]
        ];
    }
}