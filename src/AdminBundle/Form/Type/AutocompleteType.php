<?php

namespace AdminBundle\Form\Type;


use AdminBundle\AdminBundle;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;

class AutocompleteType extends AbstractType
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $manager = $this->manager;
        $originalToArray = function ($original) use ($manager, $options){
            $result = [];
            if(!$original){
                return $result;
            }

            if ($options['multiple']) {
                $isArray = is_array($original);
                if (!$isArray && substr(get_class($original), -1 * strlen($options['class'])) == $options['class']) {
                    throw new \InvalidArgumentException('A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');
                } elseif ($isArray || ($original instanceof \ArrayAccess)) {
                    $collection = $original;
                } else {
                    throw new \InvalidArgumentException('A multiple selection must be passed a collection not a single value. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');
                }
            } else {
                if (substr(get_class($original), -1 * strlen($options['class'])) == $options['class']) {
                    $collection = array($original);
                } elseif ($original instanceof \ArrayAccess) {
                    throw new \InvalidArgumentException('A single selection must be passed a single value not a collection. Make sure that form option "multiple=false" is set for many-to-one relation and "multiple=true" is set for many-to-many or one-to-many relations.');
                } else {
                    $collection = array($original);
                }
            }

            foreach ($collection as $entity) {

                $id = $entity->getId();

                try {
                    $label = (string) $entity;
                } catch (\Exception $e) {
                    throw new \RuntimeException(sprintf("Unable to convert the entity %s to String, entity must have a '__toString()' method defined", $this->className), 0, $e);
                }

                $result[] = $id;
                $result['_labels'][] = $label;
            }

            return $result;
        };

        $fromUser = function ($fromUser) use ($manager, $options) {
            // transform the string back to an array
            $collection = new \Doctrine\Common\Collections\ArrayCollection();//$this->modelManager->getModelCollectionInstance($this->className);
            $container = AdminBundle::getContainer();
            $repo = $container->get('doctrine')->getRepository($options['class_repository']);
            if (empty($fromUser)) {
                if ($options['multiple']) {
                    return $collection;
                }
                return null;
            }

            if (!$options['multiple']) {
                return $repo->find($fromUser);
            }

            if (!is_array($fromUser)) {
                throw new \UnexpectedValueException(sprintf('Value should be array, %s given.', gettype($value)));
            }


            foreach ($fromUser as $key => $id) {
                if ($key === '_labels') {
                    continue;
                }

                $collection->add($repo->find($id));
            }

            return $collection;
        };

        $builder->addModelTransformer(new CallbackTransformer($originalToArray,$fromUser),true);


        $builder->setAttribute('property', $options['property']);
        $builder->setAttribute('callback', $options['callback']);
        $builder->setAttribute('multiple', $options['multiple']);
        $builder->setAttribute('minimum_input_length', $options['minimum_input_length']);
        $builder->setAttribute('items_per_page', $options['items_per_page']);
        $builder->setAttribute('req_param_name_page_number', $options['req_param_name_page_number']);

        $builder->setAttribute(
            'disabled',
            $options['disabled']
            // NEXT_MAJOR: Remove this when bumping Symfony constraint to 2.8+
            || (array_key_exists('read_only', $options) && $options['read_only'])
        );
        $builder->setAttribute('to_string_callback', $options['to_string_callback']);

        //$builder->addModelTransformer(new IssueToNumberTransformer($this->manager,$options));

    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['admin_code'] = $options['admin_code'];
        $view->vars['property'] = $options['property'];
        $view->vars['property_url'] = $options['property_url'];
        $view->vars['property_field'] = $options['property_field'];
        $view->vars['allow_extra_fields'] = $options['allow_extra_fields'];

        $view->vars['placeholder'] = $options['placeholder'];
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['minimum_input_length'] = $options['minimum_input_length'];
        $view->vars['items_per_page'] = $options['items_per_page'];
        $view->vars['width'] = $options['width'];

        // ajax parameters
        $view->vars['url'] = $options['url'];
        $view->vars['route'] = $options['route'];
        $view->vars['req_params'] = $options['req_params'];
        $view->vars['req_param_name_search'] = $options['req_param_name_search'];
        $view->vars['req_param_name_page_number'] = $options['req_param_name_page_number'];
        $view->vars['req_param_name_items_per_page'] = $options['req_param_name_items_per_page'];
        $view->vars['quiet_millis'] = $options['quiet_millis'];
        $view->vars['cache'] = $options['cache'];

        // CSS classes
        $view->vars['container_css_class'] = $options['container_css_class'];
        $view->vars['dropdown_css_class'] = $options['dropdown_css_class'];
        $view->vars['dropdown_item_css_class'] = $options['dropdown_item_css_class'];

        $view->vars['dropdown_auto_width'] = $options['dropdown_auto_width'];


        $view->vars['context'] = $options['context'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {


        $compound = function ($options) {
            return $options['multiple'];
        };

        $resolver->setDefaults(array(
            'attr' => array(),
            'compound' => $compound,
            'model_manager' => null,
            'class' => null,
            'admin_code' => null,
            'callback' => null,
            'multiple' => false,
            'width' => '',
            'context' => '',
            'allow_extra_fields'=>true,
            'placeholder' => '',
            'minimum_input_length' => 1, //minimum 3 chars should be typed to load ajax data
            'items_per_page' => 10, //number of items per page
            'quiet_millis' => 100,
            'cache' => false,

            'to_string_callback' => null,
            'query_builder' => null,

            // ajax parameters
            'url' => '',
            'route' => array('name' => '', 'parameters' => array()),
            'req_params' => array(),
            'req_param_name_search' => 'q',
            'req_param_name_page_number' => '_page',
            'req_param_name_items_per_page' => '_limit',

            // CSS classes
            'container_css_class' => '',
            'dropdown_css_class' => '',
            'dropdown_item_css_class' => '',

            'dropdown_auto_width' => false,

            'property_field'=>'title',
            'property_url'=>'',
            'class_repository'=>null
        ));

        $resolver->setRequired(array('property'));

        // Invoke the query builder closure so that we can cache choice lists
        // for equal query builders
        $queryBuilderNormalizer = function (Options $options, $queryBuilder) {
            if (is_callable($queryBuilder)) {
                $queryBuilder = call_user_func($queryBuilder, $options['em']->getRepository($options['class']));

                if (null !== $queryBuilder && !$queryBuilder instanceof QueryBuilder) {
                    throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
                }
            }

            return $queryBuilder;
        };

        $resolver->setNormalizer('query_builder', $queryBuilderNormalizer);
        $resolver->setAllowedTypes('query_builder', array('null', 'callable', 'Doctrine\ORM\QueryBuilder'));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'autocomplete';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    //public function getParent()
    //{
    //    return CollectionType::class;
    //}
}