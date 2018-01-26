<?php

namespace AdminBundle;

use AdminBundle\DependencyInjection\Compiler\PagesPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdminBundle extends Bundle
{
    private static $containerInstance = null;

    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        parent::setContainer($container);
        self::$containerInstance = $container;
    }

    public static function getContainer()
    {
        return self::$containerInstance;
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PagesPass());
    }
}
