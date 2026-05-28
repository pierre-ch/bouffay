<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LocaleListener
{
    public function __construct(private SessionInterface $session) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $locale = $request->attributes->get('_locale');

        if (!$locale) {
            $locale = $this->session->get('_locale', 'fr');
        }

        $request->setLocale($locale);
        $this->session->set('_locale', $locale);
    }
}
