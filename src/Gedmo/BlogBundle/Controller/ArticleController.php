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
use Gedmo\BlogBundle\Entity\EmailMessage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

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

        $whitelist = array('a.created');
        $distinct = false;
        $paginator = $this->get('knp_paginator');
        $articles = $paginator->paginate(
            $q,
            $this->get('request')->query->get('page', 1),
            20,
            compact('whitelist', 'distinct')
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
            SELECT a, (SELECT COUNT(c)
              FROM GedmoBlogBundle:Comment c
              WHERE c.article = a
            ) AS num_comments
            FROM GedmoBlogBundle:Article a
            WHERE a.slug = :slug
____SQL;

        $q = $em->createQuery($dql);
        $q->setMaxResults(1);
        $q->setParameters(compact('slug'));
        $article = $q->getResult();
        if (!$article) {
            throw new NotFoundHttpException(sprintf(
                'Failed to find Article by slug:[%s]',
                $slug
            ));
        }
        $article = current($article);
        $countComments = intval($article['num_comments']);
        $article = $article[0];

        return compact('article', 'countComments');
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
            $message = new EmailMessage;
            $message->setBody($this->renderView(
                'GedmoBlogBundle:Emails:comment.html.twig',
                compact('params')
            ));
            $message->setSender($params['author']);
            $message->setEmail('gediminas.morkevicius@gmail.com');
            // internal
            $message->setTarget('gediminas.morkevicius@gmail.com');
            $message->setSubject('[blog] Comment was added');
            $message->setStatus('pending');

            if ($params['author'] != 'Gediminas') {
                $em->persist($message);
            }
            $em->persist($comment);
            $em->flush();
            $params['content'] = $this->get('markdown.parser')->transform($params['content']);
            $params['created'] = $this->get('time.templating.helper.time')->diff(new \DateTime());
            return new Response(json_encode($params));
        }
        throw new MethodNotAllowedHttpException('Invalid call context');
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
                ORDER BY c.created DESC
____SQL;
            $em = $this->get('doctrine.orm.entity_manager');
            $q = $em->createQuery($dql);
            $q->setMaxResults(10);
            $q->setFirstResult($offset);
            $q->setParameters(compact('article'));

            $time = $this->get('time.templating.helper.time');
            $md = $this->get('markdown.parser');
            $comments = array_map(function ($c) use ($time, $md) {
                $c['created'] = $time->diff($c['created']);
                $c['content'] = $md->transform($c['content']);
                return $c;
            }, $q->getArrayResult());
            return new Response(json_encode($comments));
        }
        throw new MethodNotAllowedHttpException('Invalid call context');
    }
}
