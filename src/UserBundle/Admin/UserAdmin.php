<?php
namespace UserBundle\Admin;

use AdminBundle\Form\Type\Autocomplete2Type;
use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Page\CallbackGenerator;
use AdminBundle\Page\FormHolder;
use MainBundle\Entity\Akcii;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use AdminBundle\Page\BaseAdmin;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class UserAdmin extends BaseAdmin
{
    public function getListOrder(){
        return [
            'id'=>'ASC'
        ];
    }

    public function getFormFields(FormHolder &$builderForm){
        /**
         * @var User $obj
         */
        $obj = $this->getObject();
        if($obj && $obj->hasRole('ROLE_SUPER_ADMIN')){

        }

        $builderForm
            ->tab('base')
                ->add('enabled', CheckboxType::class, ['required'=>false])
                ->add('locked', CheckboxType::class, ['required'=>false])
                ->add('type', ChoiceType::class, [
                    'label'=>'user_type',
                    'choices'=>[
                        User::FIZ_TYPE=>User::FIZ_TYPE,
                        User::URI_TYPE=>User::URI_TYPE
                    ]
                ])
                ->add('firstname', TextType::class)
                ->add('lastname', TextType::class)
                ->add('username',EmailType::class,['label'=>'E-mail'])
                ->add('plainPassword',PasswordType::class, ['required'=>($obj && $obj->getId()>0? false : true )]);
        if(!$obj && !$obj->hasRole('ROLE_SUPER_ADMIN')) {
            $builderForm->add('roles', ChoiceType::class, [
                'choices' => [
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_ADMIN' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'expanded' => true
            ]);
        }

        $builderForm

                ->add('dopEmail', EmailType::class, ['required'=>false])
                ->add('phone', TextType::class, ['required'=>false])
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
        return [
            [
                'name'=>'id'
            ],
            [
                'name'=>'username'
            ],
            [
                'name'=>'locked',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ],
            [
                'name'=>'enabled',
                'callback'=>CallbackGenerator::getBooleanCallback()
            ]
        ];
    }

    /**
     * @param Request $request
     */
    public function editAction(Request $request){
        $id = $request->get('id');

        $this->object = $this->findObject($id);

        $formHolder = new FormHolder();
        $this->getFormFields($formHolder);
        $form = $this->controller->createFormBuilder($this->object);

        if($formHolder->check()){
            foreach ($formHolder->getList() as $element){
                $form->add($element['name'],$element['type'],$element['options']);
            }
        }

        $form->add('save', SubmitType::class, array('label' => 'Сохранить','attr'=>['class'=>'btn btn-success']));
        $form = $form->getForm();

        /**
         * @var Form $form
         */
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->object = $form->getData();

            //user manager stuff
            $userManager = $this->controller->get('fos_user.user_manager');
            //check email
            $similar = $userManager->findUserByUsername($this->object->getUsername());

            if($similar && $similar->getId() != $this->object->getId()){
                $this->info->addError('E-mail уже используется');
                $this->info->data = $this->createRedirectArray(self::ACTION_EDIT,['id'=>$this->object->getId()]);
                return;
            } else {
                $this->object->setEmail($this->object->getUsername());
            }

            //pre update event
            $this->preUpdate();

            $userManager->updateUser($this->object);

            $this->info->addNotification('Элемент обновлен');
            $this->info->data = $this->createRedirectArray(self::ACTION_EDIT,['id'=>$this->object->getId()]);
            return;
        }

        $this->info->data['item'] = $this->object;
        $this->info->data['form_holder'] = $formHolder;
        $this->info->data['form'] = $form->createView();

        // breadcrumb
        $this->addBreadcrumbAction(self::ACTION_LIST);
        $this->addBreadcrumbAction(self::ACTION_EDIT,['id'=>$this->object->getId()]);
    }

    /**
     * @param Request $request
     */
    public function createAction( Request $request){

        $this->object = new $this->entity();

        $formHolder = new FormHolder();
        $this->getFormFields($formHolder);
        $form = $this->controller->createFormBuilder($this->object);
        if($formHolder->check()){
            foreach ($formHolder->getList() as $element){
                $form->add($element['name'],$element['type'],$element['options']);
            }
        }
        $form->add('save', SubmitType::class, array('label' => 'Создать','attr'=>['class'=>'btn btn-success']));
        $form = $form->getForm();

        /**
         * @var Form $form
         */
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->object = $form->getData();

            //user manager stuff
            $userManager = $this->controller->get('fos_user.user_manager');
            //check email
            $similar = $userManager->findUserByUsername($this->object->getUsername());

            if($similar && $similar->getId() != $this->object->getId()){
                $this->info->addError('E-mail уже используется');
                $this->info->data = $this->createRedirectArray(self::ACTION_EDIT,['id'=>$this->object->getId()]);
                return;
            } else {
                $this->object->setEmail($this->object->getUsername());
            }

            //pre create event
            $this->prePersist();

            $userManager->updateUser($this->object);

            $action = in_array(self::ACTION_EDIT,$this->actionList) ? self::ACTION_EDIT : self::ACTION_LIST ;

            $this->info->data = $this->createRedirectArray($action,['id'=>$this->object->getId()]);
            $this->info->data['item'] = $this->object;
            $this->info->addNotification('Элемент создан');
            return;
        }

        $this->info->data['item'] = $this->object;
        $this->info->data['form_holder'] = $formHolder;
        $this->info->data['form'] = $form->createView();

        // breadcrumb
        $this->addBreadcrumbAction(self::ACTION_LIST);
        $this->addBreadcrumbAction(self::ACTION_CREATE);
    }
}