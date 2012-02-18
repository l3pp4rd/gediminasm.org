<?php
namespace Gedmo\DemoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gedmo\DemoBundle\Entity\Category;
use Gedmo\DemoBundle\Form\CategoryType;
use Gedmo\Translatable\TranslatableListener;
use Doctrine\ORM\Query;
use Gedmo\DemoBundle\Form\ChoiceList\CategoryEntityLoader;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends Controller
{
    /**
     * @Route("/", name="demo_category_list")
     * @Template
     */
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dql = <<<____SQL
            SELECT c
            FROM GedmoDemoBundle:Category c
            ORDER BY c.lft ASC
____SQL;
        $q = $em->createQuery($dql);
        $this->setTranslatableHints($q);

        $paginator = $this->get('knp_paginator');
        $categories = $paginator->paginate(
            $q,
            $this->get('request')->query->get('page', 1),
            10
        );

        $languages = $this->getLanguages();

        return compact('categories', 'languages');
    }

    /**
     * @Route("/tree", name="demo_category_tree")
     * @Template
     */
    public function treeAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('Gedmo\DemoBundle\Entity\Category');

        $self = &$this;
        $options = array(
            'decorate' => true,
            'nodeDecorator' => function($node) use (&$self) {
                $linkUp = '<a href="' . $self->generateUrl('demo_category_move_up', array('id' => $node['id'])) . '">Up</a>';
                $linkDown = '<a href="' . $self->generateUrl('demo_category_move_down', array('id' => $node['id'])) . '">Down</a>';
                $linkNode = '<a href="' . $self->generateUrl('demo_category_show', array('slug' => $node['slug']))
                    . '">' . $node['title'] . '</a>'
                ;
                if ($node['level'] !== 0) {
                    $linkNode .= '&nbsp;&nbsp;&nbsp;' . $linkUp . '&nbsp;' . $linkDown;
                }
                return $linkNode;
            }
        );
        $query = $em
            ->createQueryBuilder()
            ->select('node')
            ->from('Gedmo\DemoBundle\Entity\Category', 'node')
            ->orderBy('node.root, node.lft', 'ASC')
            ->getQuery()
        ;
        $this->setTranslatableHints($query);
        $nodes = $query->getArrayResult();
        $tree = $repo->buildTree($nodes, $options);
        $languages = $this->getLanguages();
        $rootNodes = array_filter($nodes, function ($node) {
            return $node['level'] === 0;
        });

        return compact('tree', 'languages', 'rootNodes');
    }

    /**
     * @Route("/reorder/{id}/{direction}", name="demo_category_reorder", defaults={"direction" = "asc"})
     */
    public function reorderAction($id, $direction)
    {
        $root = $this->findNodeOr404($id);
        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Category')
        ;
        $self = &$this;
        $repo->onChildrenQuery = function (Query $q) use (&$self) {
            $self->setTranslatableHints($q);
        };

        $direction = in_array($direction, array('asc', 'desc'), false) ? $direction : 'asc';
        $repo->reorder($root, 'title', $direction);
        return $this->redirect($this->generateUrl('demo_category_tree'));
    }

    /**
     * @Route("/move-up/{id}", name="demo_category_move_up")
     */
    public function moveUpAction($id)
    {
        $node = $this->findNodeOr404($id);
        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Category')
        ;

        $repo->moveUp($node);
        return $this->redirect($this->generateUrl('demo_category_tree'));
    }

    /**
     * @Route("/move-down/{id}", name="demo_category_move_down")
     */
    public function moveDownAction($id)
    {
        $node = $this->findNodeOr404($id);
        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Category')
        ;

        $repo->moveDown($node);
        return $this->redirect($this->generateUrl('demo_category_tree'));
    }

    /**
     * @Route("/show/{slug}", name="demo_category_show")
     * @Template
     */
    public function showAction($slug)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dql = <<<____SQL
            SELECT c
            FROM GedmoDemoBundle:Category c
            WHERE c.slug = :slug
____SQL;
        $q = $em
            ->createQuery($dql)
            ->setMaxResults(1)
            ->setParameters(compact('slug'))
        ;
        $this->setTranslatableHints($q);
        $node = $q->getResult();
        if (!$node) {
            throw $this->createNotFoundException(sprintf(
                'Failed to find Category by slug:[%s]',
                $slug
            ));
        }
        $node = current($node);

        $translationRepo = $em->getRepository(
            'Gedmo:Translation'
        );
        $translations = $translationRepo->findTranslations($node);
        $pathQuery = $em
            ->getRepository('Gedmo\DemoBundle\Entity\Category')
            ->getPathQuery($node)
        ;
        $this->setTranslatableHints($pathQuery);
        $path = $pathQuery->getArrayResult();

        return compact('node', 'translations', 'path');
    }

    /**
     * @Route("/delete/{id}", name="demo_category_delete")
     */
    public function deleteAction($id)
    {
        $node = $this->findNodeOr404($id);
        $em->remove($node);
        $em->flush();
        $this->get('session')->setFlash('message', 'Category '.$node->getTitle().' was removed');

        return $this->redirect($this->generateUrl('demo_category_tree'));
    }

    /**
     * @Route("/edit/{id}", name="demo_category_edit")
     * @Template
     * @Method({"GET", "POST"})
     */
    public function editAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $node = $this->findNodeOr404($id);
        $choiceLoader = new CategoryEntityLoader($this, $em, $node);
        $choiseList = new EntityChoiceList(
            $em,
            'Gedmo\DemoBundle\Entity\Category',
            'title',
            $choiceLoader
        );
        $form = $this->createForm(new CategoryType($choiseList), $node);
        if ('POST' === $this->get('request')->getMethod()) {
            $form->bindRequest($this->get('request'), $node);
            if ($form->isValid()) {
                $em->persist($node);
                $em->flush();
                $this->get('session')->setFlash('message', 'Category was updated');
                return $this->redirect($this->generateUrl('demo_category_tree'));
            } else {
                $this->get('session')->setFlash('error', 'Fix the following errors');
            }
        }
        $form = $form->createView();
        return compact('form');
    }

    /**
     * @Route("/add", name="demo_category_add")
     * @Template
     */
    public function addAction()
    {
        $form = $this->createForm(new CategoryType, new Category)->createView();
        return compact('form');
    }

    /**
     * @Route("/save", name="demo_category_save")
     * @Method("POST")
     */
    public function saveAction()
    {
        $node = new Category;
        $form = $this->createForm(new CategoryType, $node);
        $form->bindRequest($this->get('request'), $node);
        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($node);
            $em->flush();
            $this->get('session')->setFlash('message', 'Category was added');
            return $this->redirect($this->generateUrl('demo_category_list'));
        } else {
            $this->get('session')->setFlash('error', 'Fix the following errors');
        }
        $form = $form->createView();
        return $this->render('GedmoDemoBundle:Category:add.html.twig', compact('form'));
    }

    public function setTranslatableHints(Query $query)
    {
        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
        $query->setHint(
            TranslatableListener::HINT_INNER_JOIN,
            $this->get('session')->get('gedmo.trans.inner_join', false)
        );
        $query->setHint(
            TranslatableListener::HINT_TRANSLATABLE_LOCALE,
            $this->get('request')->get('_locale', 'en')
        );
        $query->setHint(
            TranslatableListener::HINT_FALLBACK,
            $this->get('session')->get('gedmo.trans.fallback', false)
        );
    }

    private function getLanguages()
    {
        return $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Language')
            ->findAll()
        ;
    }

    private function findNodeOr404($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $q = $em->createQuery('SELECT c FROM GedmoDemoBundle:Category c WHERE c.id = :id');
        $q->setParameter('id', $id);
        $q->setMaxResults(1);
        $this->setTranslatableHints($q);
        $node = $q->getResult();
        if (!$node) {
            throw new NotFoundHttpException(sprintf(
                'Failed to find Category by id:[%s]',
                $id
            ));
        }
        return current($node);
    }
}