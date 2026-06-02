<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('/change-locale/{locale}', name: 'app_change_locale')]
    public function changeLocale(string $locale, Request $request): Response
    {
        $supportedLocales = ['fr', 'en', 'pt', 'ja', 'ht', 'ar', 'es', 'de', 'ko', 'it', 'ru', 'mq', 'gp'];
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'fr';
        }

        $request->getSession()->set('_locale', $locale);

        if ($this->getUser()) {
            $this->getUser()->setLocale($locale);
            $this->entityManager->flush();
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($this->replaceLocaleInUrl($referer, $locale));
        }

        return $this->redirectToRoute('app_home', ['_locale' => $locale]);
    }

    private function replaceLocaleInUrl(string $url, string $locale): string
    {
        $locales = ['fr', 'en', 'pt', 'ja', 'ht', 'ar', 'es', 'de', 'ko', 'it', 'ru', 'mq', 'gp'];
        foreach ($locales as $l) {
            if (preg_match('#/' . $l . '(/|$)#', $url)) {
                return preg_replace('#/' . $l . '(/|$)#', '/' . $locale . '$1', $url);
            }
        }

        if (preg_match('#^(https?://[^/]+)(/|$)#', $url, $matches)) {
            return $matches[1] . '/' . $locale . substr($url, strlen($matches[1]));
        }

        return '/' . $locale . $url;
    }
}
