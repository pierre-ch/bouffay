<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Form\ProfileFormType;
use App\Repository\FavoriteRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/mon-compte')]
class AccountController extends AbstractController
{
    #[Route('', name: 'app_account')]
    public function dashboard(OrderRepository $orderRepo, FavoriteRepository $favoriteRepo): Response
    {
        $user = $this->getUser();

        return $this->render('account/dashboard.html.twig', [
            'recentOrders'  => $orderRepo->findBy(['buyer' => $user], ['createdAt' => 'DESC'], 3),
            'favoritesCount' => $favoriteRepo->count(['user' => $user]),
        ]);
    }

    #[Route('/profil', name: 'app_account_profile')]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');

            return $this->redirectToRoute('app_account_profile');
        }

        return $this->render('account/profile.html.twig', ['form' => $form]);
    }

    #[Route('/commandes', name: 'app_account_orders')]
    public function orders(OrderRepository $orderRepo): Response
    {
        return $this->render('account/orders.html.twig', [
            'orders' => $orderRepo->findBy(['buyer' => $this->getUser()], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/commandes/{id}', name: 'app_account_order_show')]
    public function orderShow(\App\Entity\Order $order): Response
    {
        if ($order->getBuyer() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('account/order_show.html.twig', ['order' => $order]);
    }

    #[Route('/favoris', name: 'app_account_favorites')]
    public function favorites(FavoriteRepository $favoriteRepo): Response
    {
        return $this->render('account/favorites.html.twig', [
            'favorites' => $favoriteRepo->findBy(['user' => $this->getUser()]),
        ]);
    }

    #[Route('/favoris/{id}/toggle', name: 'app_account_favorite_toggle', methods: ['POST'])]
    public function favoriteToggle(
        \App\Entity\Product $product,
        FavoriteRepository $favoriteRepo,
        EntityManagerInterface $em,
        Request $request,
    ): Response {
        if (!$this->isCsrfTokenValid('favorite-' . $product->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        $existing = $favoriteRepo->findOneBy(['user' => $user, 'product' => $product]);

        if ($existing) {
            $em->remove($existing);
            $em->flush();
            $this->addFlash('success', 'Retiré des favoris.');
        } else {
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setProduct($product);
            $em->persist($favorite);
            $em->flush();
            $this->addFlash('success', 'Ajouté aux favoris.');
        }

        return $this->redirectToRoute('app_product_show', ['slug' => $product->getSlug()]);
    }
}
