<?php

namespace AdminBundle\Page;


use AdminBundle\Controller\PageController;

class DataBaseAdmin
{
    public $data;
    public $module;
    public $action;
    public $service;

    /**
     * @var array
     */
    private $error = [];

    /**
     * @var array
     */
    private $warning = [];

    /**
     * @var array
     */
    private $notification = [];

    /**
     * @var array
     */
    private $breadcrumbs = [];

    /**
     * @var array
     */
    private $menuElements = [];

    /**
     * @var array
     */
    private $menuGroups = [];

    private $menuGroupActive = '';

    private $linkOnSite = '';

    private $contentFooterElements = [];

    public function __construct()
    {
        //add base menu groups for nice order
        $this->menuGroups = [
            'menu.catalog'=>[],
            'menu.catalogparametrs'=>[],
            'menu.order'=>[],
            'menu.blog'=>[],
            'menu.connection'=>[],
            'menu.information'=>[],
            'menu.mailing'=>[],
        ];
    }


    /**
     * @return array
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function addError($error)
    {
        $this->error[] = $error;
    }

    /**
     * @param array $error
     */
    public function setError($error = [])
    {
        $this->error = $error?:[];
    }

    /**
     * @return array
     */
    public function getWarning()
    {
        return $this->warning;
    }

    /**
     * @param string $warning
     */
    public function addWarning($warning)
    {
        $this->warning[] = $warning;
    }

    /**
     * @param array $warning
     */
    public function setWarning($warning = [])
    {
        $this->warning = $warning?:[];
    }

    /**
     * @return array
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param string $notification
     */
    public function addNotification($notification)
    {
        $this->notification[] = $notification;
    }


    /**
     * @param array $notification
     */
    public function setNotification($notification = [])
    {
        $this->notification = $notification?:[];
    }

    /**
     * @return string
     */
    public function getMenuGroupActive()
    {
        return $this->menuGroupActive;
    }


    /**
     * @return array
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * @param string $title
     * @param string $url
     */
    public function addBreadcrumbs($title,$url)
    {
        $this->breadcrumbs[] = ['title'=>$title, 'url'=>$url];
    }

    /**
     * @return array
     */
    public function getMenuElements()
    {
        return $this->menuElements;
    }

    /**
     * @return array
     */
    public function getMenuGroups()
    {
        return $this->menuGroups;
    }

    public function addMenu($title,$url,$group = false, $active=false){
        if($group){
            if(!isset($this->menuGroups[$group]))
                $this->menuGroups[$group] = [];

            $this->menuGroups[$group][] = ['title'=>$title, 'url'=>$url, 'active'=>$active];
            if($active)
                $this->menuGroupActive = $group;
            return;
        }

        $this->menuElements[] = ['title'=>$title, 'url'=>$url, 'active'=>$active];
    }

    public function generatePathName($action){
        return PageController::generatePathName($this->module,$action);
    }

    /**
     * @return string
     */
    public function getLinkOnSite()
    {
        return $this->linkOnSite;
    }

    /**
     * @param string $linkOnSite
     */
    public function setLinkOnSite($linkOnSite)
    {
        $this->linkOnSite = $linkOnSite;
    }

    /**
     * @return array
     */
    public function getContentFooterElements()
    {
        return $this->contentFooterElements;
    }

    /**
     * @param string $title
     * @param string $contentFooterElements
     */
    public function addContentFooterElements($title, $contentFooterElements)
    {
        $this->contentFooterElements[] = [
            'title'=>$title,
            'content'=>$contentFooterElements
        ];
    }


}