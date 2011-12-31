<?php
namespace Gedmo\DemoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gedmo\DemoBundle\Entity\Language;
use Gedmo\DemoBundle\Form\LanguageType;

class LanguageController extends Controller
{
    /**
     * @Route("/add", name="demo_language_add")
     * @Template
     */
    public function addAction()
    {
        $form = $this->createForm(new LanguageType, new Language)->createView();
        return compact('form');
    }

    /**
     * @Route("/save", name="demo_language_save")
     * @Method("POST")
     */
    public function saveAction()
    {
        $lang = new Language;
        $form = $this->createForm(new LanguageType, $lang);
        $form->bindRequest($this->get('request'), $lang);
        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($lang);
            $em->flush();
            $this->get('session')->setFlash('message', 'Language was added');
            return $this->redirect($this->generateUrl('demo_category_tree'));
        } else {
            $this->get('session')->setFlash('error', 'Fix the following errors');
        }
        $form = $form->createView();
        return $this->render('GedmoDemoBundle:Language:add.html.twig', compact('form'));
    }
}