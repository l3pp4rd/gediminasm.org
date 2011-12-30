<?php
namespace Gedmo\DemoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gedmo\DemoBundle\Entity\Category;
use Gedmo\DemoBundle\Form\CategoryType;
use Doctrine\ORM\Query;

class CategoryController extends Controller
{
    /**
     * @Route("/", name="demo_category_list")
     * @Template()
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
     * @Template()
     */
    public function treeAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('Gedmo\DemoBundle\Entity\Category');

        $options = array(
            'decorate' => true,
            'nodeDecorator' => function($node) {
                $linkUp = '<a href="' . $this->generateUrl('demo_category_move_up', array('id' => $node['id'])) . '">Up</a>';
                $linkDown = '<a href="' . $this->generateUrl('demo_category_move_down', array('id' => $node['id'])) . '">Down</a>';
                $linkNode = '<a href="' . $this->generateUrl('demo_category_show', array('slug' => $node['slug']))
                    . '">' . $node['title'] . '</a>'
                ;
                return $linkNode . '&nbsp;&nbsp;&nbsp;' . $linkUp . '&nbsp;' . $linkDown;
            }
        );
        $query = $em
            ->createQueryBuilder()
            ->select('node')
            ->from('Gedmo\DemoBundle\Entity\Category', 'node')
            ->orderBy('node.root, node.lft', 'ASC')
            ->getQuery()
        ;
        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
        $nodes = $query->getArrayResult();
        $tree = $repo->buildTree($nodes, $options);
        $languages = $this->getLanguages();

        return compact('tree', 'languages', 'nodes');
    }

    /**
     * @Route("/revisions/{id}", name="demo_category_log")
     * @ParamConverter("node", class="GedmoDemoBundle:Category")
     * @Template()
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
     * @Route("/reorder/{direction}/{id}", name="demo_category_reorder")
     * @ParamConverter("root", class="GedmoDemoBundle:Category")
     */
    public function reorderAction(Category $root, $direction)
    {
        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Category');
        $direction = in_array($direction, array('asc', 'desc')) ? $direction : 'asc';
        $repo->reorder($root, 'title', $direction);
        return $this->redirect($this->generateUrl('test_category_tree'));
    }

    /**
     * @Route("/move/{id}/{direction}", name="demo_category_move")
     * @ParamConverter("node", class="GedmoDemoBundle:Category")
     */
    public function moveAction(Category $node, $direction)
    {
        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Category');

        $function = $direction == 'up' ? 'moveUp' : 'moveDown';
        $repo->{$function}($node);
        return $this->redirect($this->generateUrl('test_category_tree'));
    }

    /**
     * @Route("/show/{slug}", name="demo_category_show")
     * @Template()
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
     * @Route("/delete/{id}", name="demo_category_delete")
     * @ParamConverter("node", class="GedmoDemoBundle:Category")
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
     * @Route("/edit/{id}", name="demo_category_edit")
     * @ParamConverter("node", class="GedmoDemoBundle:Category")
     * @Template()
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
            $this->get('session')->setFlash('message', 'Category was saved');
            return $this->redirect($this->generateUrl('demo_category_list'));
        } else {
            $this->get('session')->setFlash('error', 'Fix the following errors');
        }
        $form = $form->createView();
        return $this->render('GedmoDemoBundle:Category:add.html.twig', compact('form'));
    }

    private function getLanguages()
    {
        return $this->get('doctrine.orm.entity_manager')
            ->getRepository('Gedmo\DemoBundle\Entity\Language')
            ->findAll()
        ;
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