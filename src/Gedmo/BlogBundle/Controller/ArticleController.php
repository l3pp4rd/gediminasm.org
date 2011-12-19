<?php

namespace Gedmo\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gedmo\BlogBundle\Entity\Article;
use Gedmo\BlogBundle\Entity\Comment;

class ArticleController extends Controller
{
    /**
     * @Route("/articles", name="blog_articles")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dql = <<<____SQL
            SELECT a, (SELECT COUNT(c)
              FROM GedmoBlogBundle:Comment c
              WHERE c.article = a
            ) AS num_comments
            FROM GedmoBlogBundle:Article a
            ORDER BY a.created DESC
____SQL;
        $q = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $articles = $paginator->paginate(
            $q,
            $this->get('request')->query->get('page', 1),
            10
        );
        return compact('articles');
    }

    /**
     * @Route("/article/{slug}", name="blog_article_view")
     * @Method("GET")
     * @Template()
     */
    public function viewAction($slug)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dql = <<<____SQL
            SELECT a
            FROM GedmoBlogBundle:Article a
            WHERE a.slug = :slug
____SQL;

        $q = $em->createQuery($dql);
        $q->setMaxResults(1);
        $q->setParameters(compact('slug'));
        $article = $q->getSingleResult();

        return compact('article');
    }

    /**
     * @Route("/article/comment", name="blog_article_comment")
     * @Method("POST")
     */
    public function addAction()
    {
        if ($this->get('request')->isXmlHttpRequest()) {
            $params = $this->get('request')->get('comment');
            $em = $this->get('doctrine.orm.entity_manager');

            $comment = new Comment;
            $comment->setArticle($em->getReference(
                'Gedmo\BlogBundle\Entity\Article',
                array('id' => $params['article_id'])
            ));
            $comment->setSubject($params['subject']);
            $comment->setAuthor($params['author']);
            $comment->setContent($params['content']);

            $violations = $this->get('validator')->validate($comment);
            if (0 !== $violations->count()) {
                throw new \RuntimeException("Invalid call context");
            }
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($comment);
            $em->flush();
            return new Response(json_encode($this->get('request')->get('comment')));
        }
        throw new \BadFunctionCallException('Invalid call context');
    }

    /**
     * @Route("/article/{id}/comments/{offset}", name="blog_article_comments")
     * @ParamConverter("article", class="GedmoBlogBundle:Article")
     */
    public function commentsAction(Article $article, $offset)
    {
        if ($this->get('request')->isXmlHttpRequest()) {
            $dql = <<<____SQL
                SELECT c
                FROM GedmoBlogBundle:Comment c
                WHERE c.article = :article
____SQL;
            $em = $this->get('doctrine.orm.entity_manager');
            $q = $em->createQuery($dql);
            $q->setMaxResults(10);
            $q->setFirstResult($offset);
            $q->setParameters(compact('article'));

            return new Response(json_encode($q->getArrayResult()));
        }
        throw new \BadFunctionCallException('Invalid call context');
    }
}
