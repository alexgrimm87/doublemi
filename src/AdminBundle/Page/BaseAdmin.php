<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 03.05.17
 * Time: 10:01
 */

namespace AdminBundle\Page;

use AdminBundle\Controller\PageController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Button;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Form;

class BaseAdmin implements PageInterface
{
    const ACTION_LIST = 'list';
    const ACTION_EDIT = 'edit';
    const ACTION_CREATE = 'create';
    const ACTION_DELETE = 'delete';

    protected $countOnPage = 32;
    protected $actionList = [];
    protected $entity = '';
    protected $action = '';
    protected $info;
    protected $object;
    /**
     * @var PageController $controller
     */
    protected $controller;
    protected $filterKey = 'filter';
    public $group;


    public function __construct($entity)
    {
        $this->entity = $entity;

        $this->info = new DataBaseAdmin();
        $this->group = false;

        $this->setActionList([
            self::ACTION_LIST,
            self::ACTION_EDIT,
            self::ACTION_CREATE,
            self::ACTION_DELETE
        ]);
    }

    public function listCheck(){
        return in_array(self::ACTION_LIST,$this->actionList);
    }

    public function editCheck(){
        return in_array(self::ACTION_EDIT,$this->actionList);
    }

    public function createCheck(){
        return in_array(self::ACTION_CREATE,$this->actionList);
    }

    public function deleteCheck(){
        return in_array(self::ACTION_DELETE,$this->actionList);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormFields(FormHolder &$builderForm){
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getListFields(){
        return [
           [
               'name'=>'id'
           ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setActionList($actions)
    {
        if(is_array($actions))
            $this->actionList = $actions;

        if(is_string($actions))
            $this->actionList = [$actions];
    }

    /**
     * {@inheritdoc}
     */
    public function getActionList()
    {
        return $this->actionList;
    }


    /**
     * {@inheritdoc}
     */
    public function actionCheck($action)
    {
        return in_array($action,$this->actionList);
    }

    /**
     * {@inheritdoc}
     */
    public function getData($action, &$controller, Request $request, $module)
    {
        if(!$this->actionCheck($action))
            throw new RouteNotFoundException();
        $this->controller = &$controller;

        $this->info->module = strtolower($module);
        $this->info->action = $action;

        $this->info->addBreadcrumbs('BC_DASHBOARD',$this->controller->generateUrl('admin_dashboard'));

        $this->{$action.'Action'}($request);

        return $this->info;
    }

    public function setGroup($groupName){
        $this->group = $groupName;
    }

    public function getObject(){
        return $this->object;
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

             //pre create event
             $this->prePersist();

             $em = $this->controller->getDoctrine()->getManager();
             $em->persist($this->object);
             $em->flush();
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

            //pre update event
            $this->preUpdate();

            $em = $this->controller->getDoctrine()->getManager();
            $em->persist($this->object);
            $em->flush();

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
    public function deleteAction(Request $request){
        $id = $request->get('id');

        $this->object = $this->findObject($id);

        $form = $this->controller->createFormBuilder($this->object);
        $form = $form->add('save', SubmitType::class, array('label' => 'Удалить','attr'=>['class'=>'btn btn-danger']))->getForm();

        /**
         * @var Form $form
         */
        $form->handleRequest($request);

        if ($form->isSubmitted()){

            //pre remove event
            $this->preRemove();

            $em = $this->controller->getDoctrine()->getManager();
            $em->remove($this->object);
            $em->flush();

            $this->info->addNotification('Элемент удален');
            $this->info->data = $this->createRedirectArray(self::ACTION_LIST);
            return;
        }

        $this->info->data['item'] = $this->object;
        $this->info->data['form'] = $form->createView();

        // breadcrumb
        $this->addBreadcrumbAction(self::ACTION_LIST);
        $this->addBreadcrumbAction(self::ACTION_EDIT,['id'=>$this->object->getId()]);
    }

    /**
     * @param Request $request
     */
    public function listAction(Request $request){
        $page = intval($request->get('_page'));
        if(!$page || $page<1)
            $page = 1;

        $limit = $request->get('_limit');
        if($limit>0)
            $this->countOnPage = intval($limit);

        $offset = $this->countOnPage * ($page - 1);

        $filter = $this->getListFilter($request, $offset);

        $data = $this->controller->adminMatching($this->filterKey,$this->getRepositoryNameFromEntityName(),$filter,$this->getListSpecParams());

        $this->info->data['items'] = $data['items'];
        $this->info->data['fields'] = $this->getListFields();

        // page navigation
        $pager = $this->controller->get('pager_generator');
        $pager->setParams($data['meta']['total'] ,$this->countOnPage);
        $this->info->data['page_info'] =  $data['meta'];
        $this->info->data['pager'] =  $pager->getPager($page);

        // breadcrumb
        $this->addBreadcrumbAction(self::ACTION_LIST);
    }

    protected function getListSpecParams(){
        return [];
    }

    protected function getListFilter(Request $request, $offset=0){
        $filter = $request->query->all();
        $filter['_sort'] = $this->getListOrder();
        $filter['_offset'] = $offset;
        $filter['_limit'] = $this->countOnPage;

        return $filter;
    }

    protected function getMutualObjects($repositoryName, $criteria, $sort){
        $choices = [];
        $objects = $this->controller->getDoctrine()->getRepository($repositoryName)->findBy($criteria,$sort);
        if($objects) {
            //normalize
            foreach ($objects as $element) {
                $choices[] = $element;
            }
        }
        return $choices;
    }

    protected function addSeoBlock(FormHolder &$formHolder){
        $formHolder->tab('seo')
                       ->add('metaTitle',TextType::class,['required'=>false])
                       ->add('metaDescription',TextareaType::class,['required'=>false])
                       ->add('metaKeyWords',TextareaType::class,['required'=>false]);

    }

    protected function prePersist(){

    }

    protected function preUpdate(){

    }

    protected function preRemove(){

    }

    /**
     * @param int $id
     * @return mixed
     */
    protected function findObject($id){
        $object = $this->controller->getDoctrine()
            ->getRepository($this->getRepositoryNameFromEntityName())
            ->find($id);

        if (!$object) {
            throw new NotFoundHttpException(
                'Элемент не найден '.$id
            );
        }

        return $object;
    }

    public function getListOrder(){
        return [
            'id'=>'ASC'
        ];
    }

    protected function createRedirectArray($action, $params=[]){
        return [
            'redirect'=>[
                'action'=>$action,
                'params'=>$params
            ]
        ];
    }

    protected function getRepositoryNameFromEntityName(){
        return str_replace('\\Entity\\',':',$this->entity);
    }

    protected function addBreadcrumbAction($action, $params=[]){
        if($this->actionCheck($action)){
            $name = $this->info->generatePathName($action);
            $this->info->addBreadcrumbs('BC_'.$name,$this->controller->generateUrl($name, $params));
        }
    }

    protected function buildPreview($href){
        return '<img src="'.$href.'" style="max-width: 200px; max-height: 200px; padding: 10px;"/>';
    }

    /**
     * Returns a rendered view.
     *
     * @param string $view       The view name
     * @param array  $parameters An array of parameters to pass to the view
     *
     * @return string The rendered view
     */
    public function renderView($view, array $parameters = array())
    {
        if(!$this->controller)
            return false;

        if ($this->controller->has('templating')) {
            return $this->controller->get('templating')->render($view, $parameters);
        }

        if (!$this->controller->has('twig')) {
            throw new \LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available.');
        }

        return $this->controller->get('twig')->render($view, $parameters);
    }
}