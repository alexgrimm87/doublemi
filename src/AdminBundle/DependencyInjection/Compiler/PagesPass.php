<?php
namespace AdminBundle\DependencyInjection\Compiler;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class PagesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('admin.pages')) {
            return;
        }


        $definition = $container->findDefinition('admin.pages');

        // find all service IDs with the admin.sheepfish tag
        $taggedServices = $container->findTaggedServiceIds('admin.sheepfish');

        $routes = [];
        foreach ($taggedServices as $id => $tags) {
            // add the transport service to the ChainTransport service
            $definition->addMethodCall('addPage', array(new Reference($id),$id));


        }
        $a = new Route(['value'=>'/test', 'name'=>'ROUTE_TEST', 'methods'=>['AdminBundle:Page:Index']]);


    }

}