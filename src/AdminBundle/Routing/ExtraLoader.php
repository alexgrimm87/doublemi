<?php
namespace AdminBundle\Routing;

use AdminBundle\AdminBundle;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ExtraLoader extends Loader
{
    private $loaded = false;

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $container = AdminBundle::getContainer();
        $pages = $container->get('admin.pages')->getPages();

        $routes = new RouteCollection();
        if($pages){

            $defaults = array(
                '_controller' => 'AdminBundle:Page:Index',
            );

            foreach ($pages as $key=>$page){
                $module = str_replace('.','-',$key);
                $routeName = sprintf('ROUTE_%s_',strtoupper(str_replace('.','_',$key)));
                foreach ($page->getActionList() as $action){
                    $path = sprintf('/%s/%s',$module,$action);
                    $route = new Route($path, $defaults);
                    $routeNameAction = $routeName.strtoupper($action);
                    $routes->add($routeNameAction, $route);
                }
            }
        }

        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'admin_page' === $type;
    }

}