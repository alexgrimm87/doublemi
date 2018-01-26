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

class ArrayObjectType extends AbstractType
{
    public static function addField($name, $type, $label='', $required=true){
        return [
            'name'=>$name,
            'type'=>$type,
            'label'=>$label?:$name,
            'required'=>$required
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformFunction = function($a){
            if(!$a)
                return [];

            foreach ($a as $i=>$value){
                if(!$value) {
                    unset($a[$i]);
                    continue;
                }


                if(is_array($value) && empty(array_filter(array_values($value)))){
                    unset($a[$i]);
                    continue;
                }
            }

            return array_values($a);
        };

        $transformer = new CallbackTransformer(
            $transformFunction,
            $transformFunction
        );
        $builder->addModelTransformer($transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fields'] = $options['fields'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'fields'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'array_object';
    }
}