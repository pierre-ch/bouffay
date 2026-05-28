<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItemStatusHistory;
use App\Repository\OrderRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/vendeur/ventes')]
#[IsGranted('ROLE_VENDEUR')]
class SellerSalesController extends AbstractController
{
    #[Route('', name: 'app_seller_sales', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $seller = $this->getUser();
        $orders = $orderRepository->findOrdersForSeller($seller);

        return $this->render('seller/sales.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'app_seller_sale_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        $seller = $this->getUser();
        
        // Verify that this order actually contains products from this seller
        $isSellerOrder = false;
        $sellerItems = [];
        foreach ($order->getOrderItems() as $item) {
            if ($item->getProduct()->getSeller() === $seller) {
                $isSellerOrder = true;
                $sellerItems[] = $item;
            }
        }

        if (!$isSellerOrder) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette commande.');
        }

        return $this->render('seller/sale_show.html.twig', [
            'order' => $order,
            'sellerItems' => $sellerItems,
        ]);
    }

    #[Route('/{id}/statut', name: 'app_seller_sale_status', methods: ['POST'])]
    public function updateStatus(Order $order, Request $request, EntityManagerInterface $em, NotificationService $notificationService): Response
    {
        $seller = $this->getUser();

        // Verify that this order contains products from this seller
        $isSellerOrder = false;
        foreach ($order->getOrderItems() as $item) {
            if ($item->getProduct()->getSeller() === $seller) {
                $isSellerOrder = true;
                break;
            }
        }

        if (!$isSellerOrder) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('update-status-' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $newStatus = $request->request->get('status');
        $validStatuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
        
        if (in_array($newStatus, $validStatuses, true)) {
            $updated = false;
            foreach ($order->getOrderItems() as $item) {
                if ($item->getProduct()->getSeller() === $seller) {
                    if ($item->getStatus() !== $newStatus) {
                        $item->setStatus($newStatus);
                        $history = new OrderItemStatusHistory();
                        $history->setOrderItem($item);
                        $history->setStatus($newStatus);
                        $em->persist($history);
                        $updated = true;
                    }
                }
            }
            if ($updated) {
                $notificationService->createNotification(
                    $order->getBuyer(),
                    'notification.order_status_updated',
                    [
                        '%order_id%' => $order->getId(),
                        '%seller%' => $seller->getFirstName() . ' ' . $seller->getLastName()
                    ],
                    $this->generateUrl('app_account_order_show', ['id' => $order->getId()])
                );
            }
            $em->flush();
            $this->addFlash('success', 'flash.order_status_updated');
        } else {
            $this->addFlash('error', 'flash.error.invalid_status');
        }

        return $this->redirectToRoute('app_seller_sale_show', ['id' => $order->getId()]);
    }
}
