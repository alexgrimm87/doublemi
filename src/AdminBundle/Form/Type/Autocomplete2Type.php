<?php

namespace AdminBundle\Form\Type;


use Doctrine\Common\Persistence\ManagerRegistry;
use AdminBundle\Form\Transformer\ObjectToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class Autocomplete2Type extends AbstractType
{
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ObjectToIdTransformer($this->registry, $options['class'], $options['multiple']);
        $builder->addModelTransformer($transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['property_url'] = $options['property_url'];
        $view->vars['property_url_params'] = $options['property_url_params'];
        $view->vars['property_field'] = $options['property_field'];
        $view->vars['required'] = $options['required'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'invalid_message' => 'The selected item does not exist',
            'multiple'=>false,
            'property_url'=>'',
            'property_url_params'=>[],
            'property_field'=>'',
            'required'=>true
        ]);
        $resolver->setRequired([
            'class',
            'multiple'
        ]);
        $resolver->setAllowedTypes('class', [
            'string',
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
        return 'autocomplete2';
    }
}