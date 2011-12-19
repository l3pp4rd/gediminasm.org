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
            ->setLinkAttribute('title', 'Articles in this blog')
        ;
        $menu->addChild('About', array('route' => 'blog_about'));
        $menu->addChild('Contact', array('route' => 'blog_contact'));

        return $menu;
    }
}