<?php
namespace Gedmo\DemoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gedmo\DemoBundle\Entity\Language;
use Gedmo\DemoBundle\Form\Language as LanguageForm;

class LanguageController extends Controller
{
    /**
     * @Route("/change/{name}", name="demo_language_change")
     */
    public function changeLanguageAction($name)
    {
        $this->get('session')->setLocale($name);
        $this->get('session')->setFlash('message', 'Language was changed');
        return $this->redirect($this->generateUrl('demo_category_tree'));
    }

    /**
     * @Route("/add", name="demo_language_add")
     * @Template()
     */
    public function addAction()
    {
        $language = new Language;
        $form = LanguageForm::create($this->get('form.context'), 'language');

        if ('POST' === $this->get('request')->getMethod()) {
            $form->bind($this->get('request'), $language);
            if ($form->isValid()) {
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($language);
                $em->flush();
                $this->get('session')->setFlash('message', 'Language was created');
                return $this->redirect($this->generateUrl('demo_category_tree'));
            }
        }
        return compact('form');
    }
}