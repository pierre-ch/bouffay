<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController extends AbstractController
{
    #[Route('/theme/toggle', name: 'app_theme_toggle', methods: ['POST'])]
    public function toggle(Request $request, EntityManagerInterface $em): Response
    {
        $current = 'light';

        if ($this->getUser()) {
            $current = $this->getUser()->getTheme();
            $next = $current === 'dark' ? 'light' : 'dark';
            $this->getUser()->setTheme($next);
            $em->flush();
        } else {
            $current = $request->getSession()->get('theme', 'light');
            $next = $current === 'dark' ? 'light' : 'dark';
            $request->getSession()->set('theme', $next);
        }

        return $this->redirect($request->headers->get('referer', '/'));
    }
}
