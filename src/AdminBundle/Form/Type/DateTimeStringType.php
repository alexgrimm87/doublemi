<?php

namespace AdminBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use AdminBundle\Form\Transformer\ObjectToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class DateTimeStringType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $transformFunction = function($a){
            return is_object($a) ? $a->format('H:i d.m.Y') : '';
        };

        $transformerFunctionBack = function ($a){
            return new \DateTime($a);
        };

        $transformer = new CallbackTransformer(
            $transformFunction,
            $transformerFunctionBack
        );
        $builder->addModelTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }
}