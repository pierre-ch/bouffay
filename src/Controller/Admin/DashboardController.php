<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Order;
use App\Entity\Review;
use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;

#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/{_locale}/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private UserRepository $userRepository,
        private ProductRepository $productRepository,
        private OrderRepository $orderRepository
    ) {}

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'users_count' => $this->userRepository->count([]),
            'products_count' => $this->productRepository->count([]),
            'orders_count' => $this->orderRepository->count([]),
            'stock_value' => $this->productRepository->getTotalStockValue(),
            'stock_by_category' => $this->productRepository->getStockValueByCategory(),
            'recent_orders' => $this->orderRepository->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Bouffay Admin')
            ->setTranslationDomain('admin');
    }

    #[Route('/admin/logs', name: 'admin_logs')]
    public function logs(): Response
    {
        $logFile = $this->getParameter('kernel.logs_dir') . '/admin.log';
        $logs = [];
        
        if (file_exists($logFile)) {
            // Read last 200 lines
            $file = new \SplFileObject($logFile, 'r');
            $file->seek(PHP_INT_MAX);
            $last_line = $file->key();
            $lines = new \LimitIterator($file, max(0, $last_line - 200));
            foreach ($lines as $line) {
                if (trim($line)) {
                    $logs[] = $line;
                }
            }
            $logs = array_reverse($logs);
        }

        return $this->render('admin/logs.html.twig', [
            'logs' => $logs
        ]);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('menu.dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('menu.back_to_site', 'fa fa-arrow-left', 'app_home');
        
        yield MenuItem::section('menu.users');
        yield MenuItem::linkToRoute('menu.accounts', 'fas fa-users', 'admin_user_index');
        
        yield MenuItem::section('menu.shop');
        yield MenuItem::linkToRoute('menu.products', 'fas fa-box', 'admin_product_index');
        yield MenuItem::linkToRoute('menu.categories', 'fas fa-tags', 'admin_category_index');
        yield MenuItem::linkToRoute('menu.orders', 'fas fa-shopping-cart', 'admin_order_index');
        
        yield MenuItem::section('menu.moderation');
        yield MenuItem::linkToRoute('menu.reviews', 'fas fa-star', 'admin_review_index');

        yield MenuItem::section('menu.system');
        yield MenuItem::linkToRoute('menu.logs', 'fas fa-file-alt', 'admin_logs');
    }
}
