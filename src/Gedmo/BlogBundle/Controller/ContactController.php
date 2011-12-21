<?php

namespace Gedmo\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gedmo\BlogBundle\Entity\EmailMessage;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    /**
     * @Route("/contact", name="blog_contact")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/contact/send", name="blog_contact_send")
     * @Method("POST")
     */
    public function sendAction()
    {
        if ($this->get('request')->isXmlHttpRequest()) {
            $params = $this->get('request')->get('message');

            $message = new EmailMessage;
            $message->setBody($this->renderView(
                'GedmoBlogBundle:Contact:email.html.twig',
                compact('params')
            ));
            $message->setSender($params['sender']);
            $message->setEmail($params['email']);
            // internal
            $message->setTarget('gediminas.morkevicius@gmail.com');
            $message->setSubject('[blog] Personal Message');
            $message->setStatus('pending');

            $violations = $this->get('validator')->validate($message);
            if (0 !== $violations->count()) {
                throw new \RuntimeException("Invalid call context");
            }

            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($message);
            $em->flush();
            // send
            return new Response(json_encode($params));
        }
        throw new \BadFunctionCallException('Invalid call context');
    }
}
