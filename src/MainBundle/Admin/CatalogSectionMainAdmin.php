<?php
namespace MainBundle\Admin;

use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use Doctrine\ORM\Mapping\Entity;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use MainBundle\Entity\CatalogSection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilder;
use AdminBundle\Page\BaseAdmin;
use Symfony\Component\HttpFoundation\Request;

class CatalogSectionMainAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'pos'=>'ASC'
        ];
    }

    protected function getListFilter(Request $request, $offset = 0)
    {
        $filter = parent::getListFilter($request, $offset);
        $filter[$this->filterKey]['main'] = '1';
        return $filter;
    }

    public function getFormFields(FormHolder &$builderForm){
        /**
         * @var CatalogSection $obj
         */
        $obj = $this->getObject();

        $builderForm->add('enabled', CheckboxType::class, ['required'=>false])
            ->add('inCatalogMenu', CheckboxType::class, ['required'=>false])
            ->add('inWeddingMenu', CheckboxType::class, ['required'=>false])
            ->add('code', TextType::class)
            ->add('title',TextType::class)
            ->add('text',CKEditorType::class,[
                'required'=>false
            ])
        ;

        if($obj){
            $choices = [];
            $objects = $this->controller->getDoctrine()->getRepository($this->getRepositoryNameFromEntityName())->findBy(['main'=>1,'parentSection'=>null],$this->getListOrder());
            if($objects){
                //normalize
                foreach ($objects as $element){
                    if($obj->getId() != $element->getId()) {
                        $choices[] = $element;
                    }
                }
                if($choices)
                    $builderForm->add('parentSection', EntityType::class, ['class'=>CatalogSection::class,'required'=>false, 'choices'=>$choices]);
            }
            if($obj->isTotalEnabled()){
                $this->info->setLinkOnSite($this->controller->get('router')->generate('catalog_main_section',['section_code'=>$obj->getCode()]));
            }
        }
        $builderForm->add('pos',IntegerType::class,['required'=>false]);
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

    protected function prePersist()
    {
        parent::prePersist(); // the autogenerated stub
        $this->object->setMain(true);
    }

    protected function preUpdate()
    {
        parent::preUpdate(); // the autogenerated stub
        $this->object->setMain(true);
    }


}