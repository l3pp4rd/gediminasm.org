<?php

namespace Gedmo\BlogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Gedmo\BlogBundle\Entity\Article;

class ArticleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Path to article sources
     * 
     * @var string
     */
    private $articleLocation;
    
    public function getOrder()
    {
        return 10;
    }
    
    public function load($manager)
    {
        $this->articleLocation = __DIR__.'/../../Resources/articles';
        
        $this->createZendDoctrineArticle($manager);
        $this->createCompilePHPArticle($manager);
        $this->createTranslatableArticle($manager);
        $this->createSluggableArticle($manager);
        $this->createSmartyArticle($manager);
        $this->createTimestampableArticle($manager);
        $this->createTreeArticle($manager);
        $this->createLoggableArticle($manager);
        
        $manager->flush();
        $manager->clear();
    }
    
    private function createSmartyArticle($manager)
    {
        $location = $this->articleLocation . '/smarty/';

        $article = new Article;
        $article->setId('smarty');
        $article->setMetaDescription('Smarty3 extension for Zend framework, with full: layout and view template support');
        $article->setTitle('Smarty 3 extension for Zend Framework');
        $article->setHeader(file_get_contents($location.'summary.html'));
        $article->setContent(file_get_contents($location.'content.html'));
        $article->setMetaKeys('smarty3, zend, framework, integrate, boostrap, configuration, extension');
        $article->setCreated(new \DateTime('2010-10-13 20:21:39'));
        
        $this->addReference('article_smarty', $article);
        $manager->persist($article);
    }
    
    private function createTranslatableArticle($manager)
    {
        $location = $this->articleLocation . '/translatable/';

        $article = new Article;
        $article->setId('translatable');
        $article->setMetaDescription('Translatable extension for Doctrine2 makes automatic record field translations and their loading depending on language used');
        $article->setTitle('Translatable behavior extension for Doctrine 2');
        $article->setHeader(file_get_contents($location.'summary.html'));
        $article->setContent(file_get_contents($location.'content.html'));
        $article->setMetaKeys('translatable, behavior, extension, doctrine2, orm, record translations, automated');
        $article->setCreated(new \DateTime('2010-09-25 22:12:33'));
        
        $this->addReference('article_translatable', $article);
        $manager->persist($article);
    }
    
    private function createZendDoctrineArticle($manager)
    {
        $location = $this->articleLocation . '/doctrine_zend/';

        $article = new Article;
        $article->setId('doctrine_zend');
        $article->setMetaDescription('How to integrate doctrine2 on zend framework');
        $article->setTitle('Doctrine 2 on Zend framework');
        $article->setHeader(file_get_contents($location.'summary.html'));
        $article->setContent(file_get_contents($location.'content.html'));
        $article->setMetaKeys('doctrine2, doctrine, zend, framework, integrate, boostrap, configuration');
        $article->setCreated(new \DateTime('2010-07-15 22:22:24'));
        
        $this->addReference('article_zend_doctrine', $article);
        $manager->persist($article);
    }
    
    private function createTreeArticle($manager)
    {
        $location = $this->articleLocation . '/tree/';

        $article = new Article;
        $article->setId('tree');
        $article->setMetaDescription('Tree - Nestedset extension for Doctrine2 makes tree implementation on entities');
        $article->setTitle('Tree - Nestedset behavior extension for Doctrine 2');
        $article->setHeader(file_get_contents($location.'summary.html'));
        $article->setContent(file_get_contents($location.'content.html'));
        $article->setMetaKeys('tree, behavior, extension, doctrine2, orm, nestedset');
        $article->setCreated(new \DateTime('2010-10-24 12:18:52'));
        
        $this->addReference('article_tree', $article);
        $manager->persist($article);
    }
    
    private function createSluggableArticle($manager)
    {
        $location = $this->articleLocation . '/sluggable/';

        $article = new Article;
        $article->setId('sluggable');
        $article->setMetaDescription('Sluggable extension for Doctrine2 makes automatic record field transformations into url friendly names');
        $article->setTitle('Sluggable behavior extension for Doctrine 2');
        $article->setHeader(file_get_contents($location.'summary.html'));
        $article->setContent(file_get_contents($location.'content.html'));
        $article->setMetaKeys('sluggable, behavior, extension, doctrine2, orm, pretty urls');
        $article->setCreated(new \DateTime('2010-09-26 12:43:59'));
        
        $this->addReference('article_sluggable', $article);
        $manager->persist($article);
    }
    
    private function createLoggableArticle($manager)
    {
        $location = $this->articleLocation . '/loggable/';

        $article = new Article;
        $article->setId('loggable');
        $article->setMetaDescription('Loggable extension for Doctrine2 tracks record changes and version management');
        $article->setTitle('Loggable behavioral extension for Doctrine2');
        $article->setHeader(file_get_contents($location.'summary.html'));
        $article->setContent(file_get_contents($location.'content.html'));
        $article->setMetaKeys('loggable, behavior, extension, doctrine2, orm, version management');
        $article->setCreated(new \DateTime('2011-03-08 10:29:44'));
        
        $this->addReference('article_loggable', $article);
        $manager->persist($article);
    }
    
    private function createCompilePHPArticle($manager)
    {
        $location = $this->articleLocation . '/compile_php/';

        $article = new Article;
        $article->setId('compile_php');
        $article->setTitle('Build php-5.3.0 - php-5.3.4-dev on Ubuntu server');
        $article->setMetaDescription('Compile php-5.3 on Ubuntu linux, create apache load configuration to load diferent PHP versions');
        $article->setHeader(file_get_contents($location.'summary.html'));
        $article->setContent(file_get_contents($location.'content.html'));
        $article->setMetaKeys('compile, build, php5, ubuntu, linux');
        $article->setCreated(new \DateTime('2010-08-16 22:26:47'));
        
        $this->addReference('article_compile_php', $article);
        $manager->persist($article);
    }
    
    private function createTimestampableArticle($manager)
    {
        $location = $this->articleLocation . '/timestampable/';

        $article = new Article;
        $article->setId('timestampable');
        $article->setTitle('Timestampable behavior extension for Doctrine 2');
        $article->setMetaDescription('Timestampable extension for Doctrine 2 helps automate update of dates');
        $article->setHeader(file_get_contents($location.'summary.html'));
        $article->setContent(file_get_contents($location.'content.html'));
        $article->setMetaKeys('timestampable, behavior, extension, doctrine2, orm');
        $article->setCreated(new \DateTime('2010-11-04 21:10:53'));
        
        $this->addReference('article_timestampable', $article);
        $manager->persist($article);
    }
}