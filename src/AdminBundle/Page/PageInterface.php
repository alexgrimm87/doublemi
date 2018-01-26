<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 03.05.17
 * Time: 09:36
 */

namespace AdminBundle\Page;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Form\FormBuilder;

interface PageInterface
{
    /**
     * Check enabled module actions
     *
     * @param $action
     * @return bool
     */
    public function actionCheck($action);

    /**
     * Update module action list
     *
     * @param string|array $actions
     * @return void
     */
    public function setActionList($actions);

    /**
     * @return array
     */
    public function getActionList();

    /**
     * get Page data according to action
     *
     * @param $action
     * @param $controller
     * @param Request $request
     * @param $module
     * @throws RouteNotFoundException
     * @return DataBaseAdmin
     */
    public function getData($action, &$controller, Request $request, $module);

    /**
     * get objects fields list
     *
     * @param FormHolder $builderForm
     * @return void
     */
    public function getFormFields(FormHolder &$builderForm);

    /**
     * get object's fields list in table view
     *
     * @return array
     */
    public function getListFields();
}