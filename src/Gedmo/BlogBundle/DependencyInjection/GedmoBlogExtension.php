<?php

namespace Gedmo\BlogBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class GedmoBlogExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        //
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return null;
    }

    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/symfony';
    }

    public function getAlias()
    {
        return 'gedmo_blog';
    }
}