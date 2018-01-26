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

class BouquetSubscribeAdmin extends BaseAdmin
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
            ->add('email', EmailType::class)
            ->add('user', Autocomplete2Type::class,[
                'class'=>'UserBundle:User',
                'multiple'=>false,
                'property_url' => 'ROUTE_APP_USERLIST_LIST',
                'property_url_params'=>[],
                'property_field'=>'username',
                'required' => false
            ])
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
                'name'=>'email'
            ],
            [
                'name'=>'date',
                'format'=>'H:i d/m/Y'
            ]
        ];
    }
}