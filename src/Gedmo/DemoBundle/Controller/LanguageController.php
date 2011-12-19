<?php
namespace Gedmo\TestExtensionsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gedmo\TestExtensionsBundle\Entity\Language;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Gedmo\TestExtensionsBundle\Form\Language as LanguageForm;

class LanguageController extends Controller
{
    /**
     * @extra:Route("/change/{name}", name="test_language_change")
     */
    public function changeLanguageAction($name)
    {
        $this->get('session')->setLocale($name);
        $this->get('session')->setFlash('message', 'Language was changed');
        return new RedirectResponse($this->generateUrl('test_category_tree'));
    }
    
    /**
     * @extra:Route("/add", name="test_language_add")
     * @extra:Template()
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
                return new RedirectResponse($this->generateUrl('test_category_tree'));
            }
        }
        return compact('form');
    }
}