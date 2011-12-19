<?php

namespace Gedmo\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AboutController extends Controller
{
    /**
     * @Route("/about", name="blog_about")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
