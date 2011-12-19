<?php

namespace Gedmo\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Gedmo\BlogBundle\Entity\Article,
    Gedmo\BlogBundle\Entity\Comment,
    Zend\Paginator\Paginator,
    Gedmo\AdminBundle\Form\Article as ArticleForm;
    
class ArticleController extends Controller
{
    const ARTICLE_ENTITY = 'Gedmo\BlogBundle\Entity\Article';
    
    /**
     * @extra:Route("/", name="admin_articles")
     * @extra:Template()
     */
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dql = "SELECT a FROM GedmoBlogBundle:Article a";
        $q = $em->createQuery($dql);
        
        $adapter = $this->get('knplabs_paginator.adapter');
        $adapter->setQuery($q);
        $adapter->setDistinct(false);

        $paginator = new Paginator($adapter);
        $paginator->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);
        
        return compact('paginator');
    }
    
    /**
     * @extra:Route("/article/add", name="admin_article_add")
     * @extra:Template()
     */
    public function addAction()
    {
        $article = new Article();
        $form = ArticleForm::create($this->get('form.context'), 'article');
        
        if ('POST' === $this->get('request')->getMethod()) {
            $form->bind($this->get('request'), $article); 
            if ($form->isValid()) {
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($article);
                $em->flush();
                $this->get('session')->setFlash('message', 'Article was created');
                return new RedirectResponse($this->generateUrl('admin_articles'));
            }
        }
        return compact('form');
    }
    
    /**
     * @extra:Route("/article/edit/{id}", name="admin_article_edit")
     * @extra:ParamConverter("article", class="GedmoBlogBundle:Article")
     * @extra:Template()
     */
    public function editAction(Article $article)
    {
        $form = ArticleForm::create($this->get('form.context'), 'article');
        $form->bind($this->get('request'), $article);
        if ('POST' === $this->get('request')->getMethod()) {
            if ($form->isValid()) {
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($article);
                $em->flush();
                $this->get('session')->setFlash('message', 'Article was saved');
                return new RedirectResponse($this->generateUrl('admin_articles'));
            }
        }
        return compact('form');
    }
    
    /**
     * @extra:Route("/article/delete/{id}", name="admin_article_delete")
     * @extra:ParamConverter("article", class="GedmoBlogBundle:Article")
     */
    public function deleteAction(Article $article)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $em->remove($article);
        $em->flush();
        $this->get('session')->setFlash('message', 'Article was deleted');
        
        return new RedirectResponse($this->generateUrl('admin_articles'));
    }
}
