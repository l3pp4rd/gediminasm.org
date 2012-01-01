<?php

namespace Gedmo\DemoBundle\Listener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session;
use Stof\DoctrineExtensionsBundle\Listener\TranslationListener;

class TranslationFallbackListener implements EventSubscriberInterface
{
    private $translatable;
    private $session;

    public function __construct(Session $session, TranslationListener $translatable)
    {
        $this->session = $session;
        $this->translatable = $translatable;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $fallback = $this->session->get('gedmo.trans.fallback', false);
            $this->translatable->setTranslationFallback($fallback);
        }
    }

    static public function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 128),
        );
    }
}