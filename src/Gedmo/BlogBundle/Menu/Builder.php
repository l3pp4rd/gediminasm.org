<?php

namespace Gedmo\BlogBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setCurrentUri($this->container->get('request')->getRequestUri());

        $menu
            ->addChild('Articles', array('route' => 'home'))
            ->setLinkAttribute('title', 'Articles')
            ->setLinkAttribute('target', '_top')
        ;
        $menu
            ->addChild('About me', array('route' => 'blog_about'))
            ->setLinkAttribute('target', '_top')
            ->setLinkAttribute('rel', 'author')
        ;
        $menu->addChild('Contact', array('route' => 'blog_contact'));
        $menu
            ->addChild('Extension Demo', array('route' => 'demo_category_tree'))
            ->setLinkAttribute('target', '_top')
        ;

        return $menu;
    }
}