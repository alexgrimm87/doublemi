<?php
namespace BlogBundle\Admin;

use AdminBundle\Form\Type\Autocomplete2Type;
use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Page\BaseAdmin;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use BlogBundle\BlogBundle;
use BlogBundle\Entity\Post;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PostAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'created'=>'DESC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){

        /**
         * @var Post $obj
         */
        $obj = $this->getObject();
        if($obj->isEnabled()){
            $this->info->setLinkOnSite(
                $this->controller->get('router')->generate(
                    ($obj->isArchive()?'blog_archive_post':'blog_root_post'),
                    ['post'=>$obj->getCode()]
                )
            );
        }

        $builderForm
            ->tab('base')
            ->add('enabled', CheckboxType::class, ['required'=>false])
            ->add('favorite', CheckboxType::class, ['required'=>false])
            ->add('archive', CheckboxType::class, ['required'=>false])
            ->add('code', TextType::class)
            ->add('title',TextType::class)
            ->add('tags', Autocomplete2Type::class,[
                'class'=>'BlogBundle:Tag',
                'multiple'=>true,
                'property_url' => 'ROUTE_BLOG_TAG_LIST',
                'property_url_params'=>[],
                'property_field'=>'text',
                'required' => false
            ])
            ->add('picture',FileselectType::class)
            ->add('description',TextareaType::class)
            ->add('text',CKEditorType::class,[
                'required'=>false
            ])

        ;

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
                'name'=>'favorite',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'archive',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'created'
            ]
        ];
    }



    protected function preRemove()
    {
        parent::preRemove();
        $this->clearCache();
    }

    protected function prePersist()
    {
        parent::prePersist();
        $this->clearCache();
    }

    protected function preUpdate()
    {
        parent::preUpdate();
        $this->clearCache();
    }

    public function clearCache(){
        $cache = $this->controller->get('cache');
        $cache->setNamespace(BlogBundle::CACHE_NAMESPACE);
        $id = date('Y-m-d');
        $cache->delete($id);
    }
}