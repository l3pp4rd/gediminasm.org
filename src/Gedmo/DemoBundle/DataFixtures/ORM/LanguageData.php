<?php

namespace Gedmo\TestExtensionsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Gedmo\TestExtensionsBundle\Entity\Language;

class LanguageData extends AbstractFixture implements OrderedFixtureInterface
{    
    public function getOrder()
    {
        return 20;
    }
    
    public function load($em)
    {
        $lang0 = new Language;
        $lang0->setTitle('En');
        
        $lang1 = new Language;
        $lang1->setTitle('De');
        
        $em->persist($lang0);
        $em->persist($lang1);
        $em->flush();
        $em->clear();
    }
}