<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\ProductRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        $latestProducts = $productRepository->findBy(
            ['status' => 'active'],
            ['createdAt' => 'DESC'],
            4
        );

        return $this->render('home/index.html.twig', [
            'latestProducts' => $latestProducts,
        ]);
    }
}
