<?php
namespace Gedmo\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gedmo\DemoBundle\Entity\Category;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Gedmo\DemoBundle\Form\Category as CategoryForm;

class CategoryController extends Controller
{
    /**
     * @extra:Route("/", name="test_category_list")
     * @extra:Template()
     */
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $dql = "SELECT c FROM GedmoDemoBundle:Category c";
        $dql .= " ORDER BY c.lft ASC";

        $q = $em->createQuery($dql);
        $adapter = $this->get('knplabs_paginator.adapter');
        $adapter->setQuery($q);

        $paginator = new Paginator($adapter);
        $paginator->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        $languages = $this->getLanguages();

        return compact('paginator', 'languages');
    }

    /**
     * @extra:Route("/tree", name="test_category_tree")
     * @extra:Template()
     */
    public function treeAction()
    {
        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Category');

        $rootNodes = $repo->getRoodNodes();
        $tree = $this->buildTree($rootNodes, $repo);
        $languages = $this->getLanguages();

        return compact('tree', 'languages', 'rootNodes');
    }

    /**
     * @extra:Route("/revisions/{id}", name="test_category_log")
     * @extra:ParamConverter("node", class="GedmoDemoBundle:Category")
     * @extra:Template()
     */
    public function revisionsAction($node)
    {
        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Category');

        $query = $repo->getRevisionListQuery($node);
        $adapter = $this->get('knplabs_paginator.adapter');
        $adapter->setQuery($query);
        $adapter->setDistinct(false);

        $paginator = new Paginator($adapter);
        $paginator->setCurrentPageNumber($this->get('request')->query->get('page', 1));
        $paginator->setItemCountPerPage(5);
        $paginator->setPageRange(5);

        return compact('paginator', 'node');
    }

    /**
     * @extra:Route("/reorder/{direction}/{id}", name="test_category_reorder")
     * @extra:ParamConverter("root", class="GedmoDemoBundle:Category")
     */
    public function reorderAction(Category $root, $direction)
    {
        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Category');
        $direction = in_array($direction, array('asc', 'desc')) ? $direction : 'asc';
        $repo->reorder($root, 'title', $direction);
        return new RedirectResponse($this->generateUrl('test_category_tree'));
    }

    /**
     * @extra:Route("/move/{id}/{direction}", name="test_category_move")
     * @extra:ParamConverter("node", class="GedmoDemoBundle:Category")
     */
    public function moveAction(Category $node, $direction)
    {
        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Category');

        $function = $direction == 'up' ? 'moveUp' : 'moveDown';
        $repo->{$function}($node);
        return new RedirectResponse($this->generateUrl('test_category_tree'));
    }

    /**
     * @extra:Route("/show/{slug}", name="test_category_show")
     * @extra:Template()
     */
    public function showAction($slug)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $translationRepo = $em->getRepository('Stof\DoctrineExtensionsBundle\Entity\Translation');
        $category = $translationRepo->findObjectByTranslatedField(
            'slug',
            $slug,
            'Gedmo\DemoBundle\Entity\Category'
        );
        $translations = $translationRepo->findTranslations($category);
        $path = $em->getRepository('Gedmo\DemoBundle\Entity\Category')
            ->getPath($category);

        return compact('category', 'translations', 'path');
    }

    /**
     * @extra:Route("/delete/{id}", name="test_category_delete")
     * @extra:ParamConverter("node", class="GedmoDemoBundle:Category")
     */
    public function deleteAction(Category $node)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $em->remove($node);
        $em->flush();
        $this->get('session')->setFlash('message', 'Category was deleted');

        return new RedirectResponse($this->generateUrl('test_category_tree'));
    }

    /**
     * @extra:Route("/edit/{id}", name="test_category_edit")
     * @extra:ParamConverter("node", class="GedmoDemoBundle:Category")
     * @extra:Template()
     */
    public function editAction(Category $node)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('Gedmo\\DemoBundle\\Entity\\Category');
        $form = CategoryForm::create($this->get('form.context'), 'category');
        $form->bind($this->get('request'), $node);
        $form->add(
            new ChoiceField('parent', array(
                'choices' => $repo->findAllParentChoises(null),
                'value_transformer' => new Choise($repo),
                'empty_value' => '---',
                'required' => false,
            ))
        );
        if ('POST' === $this->get('request')->getMethod()) {
            $form->bind($this->get('request'), $node);
            if ($form->isValid()) {
                $em->persist($node);
                $em->flush();
                $this->get('session')->setFlash('message', 'Category was saved');
                return new RedirectResponse($this->generateUrl('test_category_tree'));
            }
        }
        return compact('form');
    }

    /**
     * @extra:Route("/add", name="test_category_add")
     * @extra:Template("GedmoDemoBundle:Category:add.html.twig", vars={"form"})
     */
    public function addAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('Gedmo\\DemoBundle\\Entity\\Category');
        $category = new Category;

        $form = CategoryForm::create($this->get('form.context'), 'category', array('repo' => $repo));

        if ('POST' === $this->get('request')->getMethod()) {
            $form->bind($this->get('request'), $category);
            if ($form->isValid()) {
                $em->persist($category);
                $em->flush();
                $this->get('session')->setFlash('message', 'Category was created');
                return new RedirectResponse($this->generateUrl('test_category_tree'));
            }
        }
        return compact('form');
    }

    private function getLanguages()
    {
        return $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Language')
            ->findAll();
    }

    private function buildTree($nodes, $repo) {
        $result = '<ul>';
        foreach ($nodes as $node) {
            $linkUp = '<a href="' . $this->generateUrl('test_category_move', array('id' => $node->getId(), 'direction' => 'up')) . '">Up</a>';
            $linkDown = '<a href="' . $this->generateUrl('test_category_move', array('id' => $node->getId(), 'direction' => 'down')) . '">Down</a>';
            $result .= '<li>' . $node->getTitle() . '&nbsp;&nbsp;&nbsp;' . $linkUp . '&nbsp;' . $linkDown;
            if ($repo->childCount($node, false)) {
                $result .= $this->buildTree($repo->children($node, true), $repo);
            }
            $result .= '</li>';
        }
        $result .= '</ul>';
        return $result;
    }
}