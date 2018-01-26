<?php

namespace UserBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use UserBundle\Entity\User;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('username');
        $builder->add('firstname',TextType::class,['required'=>true])
                ->add('lastname',TextType::class,['required'=>true])
                ->add('useterms',CheckboxType::class,['required'=>true])
                ->add('type',HiddenType::class )
                ->add('phone', TextType::class , ['attr'=>['class'=>'el-input tel-mask']])
                ->add('inn', TextType::class )
                ->add('orgName', TextType::class )
                ->add('uriAddress', TextType::class )
                ->add('factAddress', TextType::class )
                ->add('uriPhone', TextType::class , ['attr'=>['class'=>'el-input tel-mask']])
                ->add('bankName', TextType::class )
                ->add('bankCheckingIndex', TextType::class )
                ->add('bankCorrespondentIndex', TextType::class )
                ->add('bankIndex', TextType::class )
        ;
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }

    // For Symfony 2.x
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}