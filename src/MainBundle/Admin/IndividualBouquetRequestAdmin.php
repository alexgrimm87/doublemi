<?php
namespace MainBundle\Admin;

use AdminBundle\Form\Type\ArrayObjectType;
use AdminBundle\Page\FormHolder;
use MainBundle\Entity\IndividualBouquetRequest;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use AdminBundle\Page\BaseAdmin;

class IndividualBouquetRequestAdmin extends BaseAdmin
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
            ->add('deliveryDate',DateTimeType::class,["required"=>false])
            ->add('email',EmailType::class,["required"=>false])
            ->add('phoneSender',TextType::class,["required"=>false])
            ->add('phoneRecipient',TextType::class,["required"=>false])
            ->add('comment',TextareaType::class,["required"=>false])
            ->add('negativeFlower',TextareaType::class,["required"=>false])
            ->add('occasion',TextType::class,["required"=>false])
            ->add('target',TextType::class,["required"=>false])
            ->add('payment', ChoiceType::class, ['choices'=>[
                IndividualBouquetRequest::ONLINE_PAYMENT=> IndividualBouquetRequest::ONLINE_PAYMENT,
                IndividualBouquetRequest::CASH_PAYMENT => IndividualBouquetRequest::CASH_PAYMENT
            ]])
            ->add('status', ChoiceType::class,[
                'choices'=>[
                    IndividualBouquetRequest::NEW_STATUS => IndividualBouquetRequest::NEW_STATUS,
                    IndividualBouquetRequest::PAID_STATUS => IndividualBouquetRequest::PAID_STATUS,
                    IndividualBouquetRequest::IN_WORK_STATUS => IndividualBouquetRequest::IN_WORK_STATUS,
                    IndividualBouquetRequest::DONE_STATUS => IndividualBouquetRequest::DONE_STATUS
                ]
            ])
            ->add('price',IntegerType::class,["required"=>false])
            ->add('params',ArrayObjectType::class,[
                "fields" => [
                    ArrayObjectType::addField('title','text'),
                    ArrayObjectType::addField('markup','number','',false)
                ]
            ])
        ;
    }

    public function getListFields(){
        return [
            [
                'name'=>'id'
            ],
            [
                'name'=>'email'
            ],
            [
                'name'=>'deliveryDate',
                'format'=>'H:i d/m/Y'
            ],
            [
                'name'=>'date',
                'format'=>'H:i d/m/Y'
            ]
        ];
    }
}