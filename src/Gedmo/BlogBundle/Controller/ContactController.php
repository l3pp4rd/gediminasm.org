<?php

namespace Gedmo\BlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Gedmo\BlogBundle\Model\ContactMessage,
    Gedmo\BlogBundle\Form\Contact as ContactForm;

class ContactController extends Controller
{
    /**
     * @Route("/contact", name="blog_contact")
     * @Template()
     */
    public function indexAction()
    {
        $message = new ContactMessage();

        $form = ContactForm::create($this->get('form.context'), 'contact');

        if ('POST' === $this->get('request')->getMethod()) {
            $form->bind($this->get('request'), $message);
            if ($form->isValid()) {
                $this->sendMessage($message);
            } else {
                //
            }
        }

        return compact('form');
    }

    private function sendMessage(ContactMessage $message)
    {
        $mailer = $this->get('mailer');
        $email = \Swift_Message::newInstance()
            ->setSubject('Blog message')
            ->setCharset('utf-8')
            ->setReplyTo(array($message->getEmail() => $message->getSender()))
            ->setSender($message->getEmail(), $message->getSender())
            ->setTo(array('gediminas.morkevicius@gmail.com' => 'Gediminas'))
            ->setBody($this->renderView('GedmoBlogBundle:Contact:email.html.twig', compact('message')));
        return (bool)$mailer->send($email);
    }
}
