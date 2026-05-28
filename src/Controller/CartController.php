<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Form\CartFormType;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panier')]
#[IsGranted('ROLE_CLIENT')]
class CartController extends AbstractController
{
    #[Route('', name: 'app_cart_show', methods: ['GET'])]
    public function show(CartRepository $cartRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setCreatedAt(new \DateTimeImmutable());
            $em->persist($cart);
            $em->flush();
        }

        $total = 0;
        foreach ($cart->getCartItems() as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }

        $form = $this->createForm(CartFormType::class, $cart);

        return $this->render('cart/show.html.twig', [
            'cart' => $cart,
            'form' => $form,
            'total' => $total,
        ]);
    }

    #[Route('/ajouter/{productId}', name: 'app_cart_add', methods: ['POST'])]
    public function addItem(
        int $productId,
        Request $request,
        ProductRepository $productRepository,
        CartRepository $cartRepository,
        EntityManagerInterface $em,
    ): Response {
        $product = $productRepository->find($productId);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $quantity = (int) ($request->request->get('quantity') ?? 1);
        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($product->getStock() !== null && $quantity > $product->getStock()) {
            $this->addFlash('error', 'flash.error.stock_insufficient');
            return $this->redirectToRoute('app_product_show', ['slug' => $product->getSlug()]);
        }

        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setCreatedAt(new \DateTimeImmutable());
            $em->persist($cart);
        }

        $existingItem = null;
        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $existingItem = $item;
                break;
            }
        }

        if ($existingItem) {
            $newQuantity = $existingItem->getQuantity() + $quantity;
            if ($product->getStock() !== null && $newQuantity > $product->getStock()) {
                $this->addFlash('error', 'flash.error.stock_insufficient');
                return $this->redirectToRoute('app_cart_show');
            }
            $existingItem->setQuantity($newQuantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cart->addCartItem($cartItem);
            $em->persist($cartItem);
        }

        $em->flush();
        $this->addFlash('success', 'flash.cart_added');

        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/mettre-a-jour', name: 'app_cart_update', methods: ['POST'])]
    public function updateQuantities(
        Request $request,
        CartRepository $cartRepository,
        EntityManagerInterface $em,
    ): Response {
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart) {
            return $this->redirectToRoute('app_cart_show');
        }

        $form = $this->createForm(CartFormType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hasStockError = false;

            foreach ($cart->getCartItems() as $item) {
                if ($item->getQuantity() <= 0) {
                    $cart->removeCartItem($item);
                } else {
                    $product = $item->getProduct();
                    if ($product->getStock() !== null && $item->getQuantity() > $product->getStock()) {
                        $this->addFlash('error', sprintf(
                            'Stock insuffisant pour %s (disponible: %d)',
                            $product->getName(),
                            $product->getStock()
                        ));
                        $hasStockError = true;
                        $item->setQuantity($product->getStock());
                    }
                }
            }

            if (!$hasStockError) {
                $em->flush();
                $this->addFlash('success', 'flash.cart_updated');
            } else {
                $em->flush();
            }
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'flash.error.form_invalid');
        }

        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/supprimer/{cartItemId}', name: 'app_cart_remove', methods: ['POST'])]
    public function removeItem(
        int $cartItemId,
        Request $request,
        CartRepository $cartRepository,
        EntityManagerInterface $em,
    ): Response {
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart) {
            return $this->redirectToRoute('app_cart_show');
        }

        foreach ($cart->getCartItems() as $item) {
            if ($item->getId() === $cartItemId) {
                if (!$this->isCsrfTokenValid('delete-' . $cartItemId, $request->request->get('_token'))) {
                    throw $this->createAccessDeniedException();
                }

                $cart->removeCartItem($item);
                $em->flush();
                $this->addFlash('success', 'flash.cart_removed');
                break;
            }
        }

        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/vider', name: 'app_cart_clear', methods: ['POST'])]
    public function clearCart(
        Request $request,
        CartRepository $cartRepository,
        EntityManagerInterface $em,
    ): Response {
        if (!$this->isCsrfTokenValid('clear-cart', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if ($cart) {
            foreach ($cart->getCartItems() as $item) {
                $cart->removeCartItem($item);
            }

            $em->flush();
            $this->addFlash('success', 'flash.cart_emptied');
        }

        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/commande', name: 'app_cart_checkout', methods: ['GET'])]
    public function checkout(CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart || $cart->getCartItems()->isEmpty()) {
            $this->addFlash('warning', 'flash.error.cart_empty');

            return $this->redirectToRoute('app_cart_show');
        }

        $total = 0;
        foreach ($cart->getCartItems() as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }

        return $this->render('cart/checkout.html.twig', [
            'cart' => $cart,
            'user' => $user,
            'total' => $total,
        ]);
    }

    #[Route('/valider', name: 'app_cart_validate', methods: ['POST'])]
    public function validateOrder(
        Request $request,
        CartRepository $cartRepository,
        EntityManagerInterface $em,
    ): Response {
        if (!$this->isCsrfTokenValid('validate-order', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart || $cart->getCartItems()->isEmpty()) {
            $this->addFlash('warning', 'flash.error.cart_empty');

            return $this->redirectToRoute('app_cart_show');
        }

        $addressId = (int) $request->request->get('address_id');
        $selectedAddress = null;

        foreach ($user->getAddresses() as $address) {
            if ($address->getId() === $addressId) {
                $selectedAddress = $address;
                break;
            }
        }

        if (!$selectedAddress) {
            $this->addFlash('error', 'flash.error.address_missing');

            return $this->redirectToRoute('app_cart_checkout');
        }

        $order = new Order();
        $order->setBuyer($user);
        $order->setAddress($selectedAddress);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setStatus('pending');

        $total = 0;
        foreach ($cart->getCartItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $orderItem = new OrderItem();
            $orderItem->setOrderRef($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setUnitPrice((string) $product->getPrice());

            $order->addOrderItem($orderItem);
            $em->persist($orderItem);

            // Diminuer le stock
            if ($product->getStock() !== null) {
                $product->setStock($product->getStock() - $cartItem->getQuantity());
            }

            $total += $product->getPrice() * $cartItem->getQuantity();
        }

        $order->setTotalPrice((string) $total);
        $em->persist($order);

        foreach ($cart->getCartItems() as $item) {
            $cart->removeCartItem($item);
        }

        $em->flush();

        $this->addFlash('success', 'flash.order_created');

        return $this->redirectToRoute('app_account_orders');
    }
}
