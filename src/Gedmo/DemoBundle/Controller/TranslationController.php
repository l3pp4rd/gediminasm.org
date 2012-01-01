<?php

namespace Gedmo\DemoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TranslationController extends Controller
{
    /**
     * @Route("/fallback", name="demo_fallback_translation")
     */
    public function fallbackAction()
    {
        $s = $this->get('session');
        $fallback = $s->get('gedmo.trans.fallback', false);
        $fallback = $fallback ? false : true;
        $s->set('gedmo.trans.fallback', $fallback);
        return $this->redirect($this->generateUrl('demo_category_tree'));
    }

    /**
     * @Route("/inner-strategy", name="demo_inner_strategy")
     */
    public function innerAction()
    {
        $s = $this->get('session');
        $strategy = $s->get('gedmo.trans.inner_join', false);
        $strategy = $strategy ? false : true;
        $s->set('gedmo.trans.inner_join', $strategy);
        return $this->redirect($this->generateUrl('demo_category_tree'));
    }
}