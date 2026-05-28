<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Favorite;
use App\Form\AddressFormType;
use App\Form\ProfileFormType;
use App\Repository\AddressRepository;
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
    public function dashboard(OrderRepository $orderRepo, FavoriteRepository $favoriteRepo, AddressRepository $addressRepo): Response
    {
        $user = $this->getUser();

        return $this->render('account/dashboard.html.twig', [
            'recentOrders'  => $orderRepo->findBy(['buyer' => $user], ['createdAt' => 'DESC'], 3),
            'favoritesCount' => $favoriteRepo->count(['user' => $user]),
            'addressesCount' => $addressRepo->count(['user' => $user]),
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
            $this->addFlash('success', 'flash.profile_updated');

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
            $this->addFlash('success', 'flash.favorite_removed');
        } else {
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setProduct($product);
            $em->persist($favorite);
            $em->flush();
            $this->addFlash('success', 'flash.favorite_added');
        }

        return $this->redirectToRoute('app_product_show', ['slug' => $product->getSlug()]);
    }

    #[Route('/adresses', name: 'app_account_addresses')]
    public function listAddresses(AddressRepository $addressRepo): Response
    {
        $user = $this->getUser();

        return $this->render('account/addresses.html.twig', [
            'addresses' => $addressRepo->findBy(['user' => $user]),
        ]);
    }

    #[Route('/adresses/ajouter', name: 'app_account_address_add', methods: ['GET', 'POST'])]
    public function addAddress(Request $request, EntityManagerInterface $em): Response
    {
        $address = new Address();
        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $address->setUser($user);

            if ($address->isDefault()) {
                foreach ($user->getAddresses() as $userAddress) {
                    $userAddress->setIsDefault(false);
                }
            }

            $em->persist($address);
            $em->flush();
            $this->addFlash('success', 'flash.address_added');

            return $this->redirectToRoute('app_account_addresses');
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form,
            'title' => 'Ajouter une adresse',
        ]);
    }

    #[Route('/adresses/{id}/modifier', name: 'app_account_address_edit', methods: ['GET', 'POST'])]
    public function editAddress(Address $address, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($address->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($address->isDefault()) {
                foreach ($user->getAddresses() as $userAddress) {
                    if ($userAddress !== $address) {
                        $userAddress->setIsDefault(false);
                    }
                }
            }

            $em->flush();
            $this->addFlash('success', 'flash.address_updated');

            return $this->redirectToRoute('app_account_addresses');
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form,
            'title' => 'Modifier l\'adresse',
        ]);
    }

    #[Route('/adresses/{id}/supprimer', name: 'app_account_address_delete', methods: ['POST'])]
    public function deleteAddress(Address $address, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($address->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete-address-' . $address->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($address);
        $em->flush();
        $this->addFlash('success', 'flash.address_deleted');

        return $this->redirectToRoute('app_account_addresses');
    }

    #[Route('/adresses/{id}/par-defaut', name: 'app_account_address_default', methods: ['POST'])]
    public function setDefaultAddress(Address $address, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($address->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('default-address-' . $address->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        foreach ($user->getAddresses() as $userAddress) {
            $userAddress->setIsDefault(false);
        }

        $address->setIsDefault(true);
        $em->flush();
        $this->addFlash('success', 'flash.address_default_updated');

        return $this->redirectToRoute('app_account_addresses');
    }
}

