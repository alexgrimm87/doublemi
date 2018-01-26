<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 03.05.17
 * Time: 12:32
 */

namespace AdminBundle\Service;


use AdminBundle\Page\BaseAdmin;
use AdminBundle\Page\DataBaseAdmin;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use AdminBundle\Controller\PageController;

class PagesService
{
    private $pagesList;
    private $menuAddList;

    public function __construct()
    {
        $this->pagesList = [];
    }

    public function addPage($page, $module){
        $this->pagesList[$module] = $page;
    }

    public function getPages(){
        return $this->pagesList;
    }

    public function getPage($module){
        $module = strtolower(str_replace(['-','_'],'.',$module));
        if(isset($this->pagesList[$module]))
            return $this->pagesList[$module];

        throw new RouteNotFoundException();
    }

    public function addMenu($title,$route,$group = false){
        $this->menuAddList[] = [
            'title'=>$title,
            'route'=>$route,
            'group'=>$group
        ];
    }

    public function getMenu(DataBaseAdmin &$info,PageController $controller){
        if(!$this->pagesList)
            return;

        /**
         * @var BaseAdmin $item
         */
        $module = str_replace(['_','-'],'.',$info->module);
        foreach ($this->pagesList as $key=>$item){
            //check if list event available

            if(in_array(BaseAdmin::ACTION_LIST,$item->getActionList())){

                $route = PageController::generatePathName($key,BaseAdmin::ACTION_LIST);
                $info->addMenu($key,$controller->generateUrl($route),$item->group,($module == $key));
            }
        }
        if($this->menuAddList){
            foreach ($this->menuAddList as $item){
                $info->addMenu($item['title'],$controller->generateUrl($item['route']),$item['group']);
            }
        }
    }
}