<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\ProductService;
use App\Services\OrderService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected UserService $userService;
    protected ProductService $productService;
    protected OrderService $orderService;

    public function __construct(
        UserService $userService,
        ProductService $productService,
        OrderService $orderService
    ) {
        $this->userService = $userService;
        $this->productService = $productService;
        $this->orderService = $orderService;
    }

    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $statistics = [
            'users' => [
                'total' => \App\Models\User::count(),
                'active' => $this->userService->getActiveUsersCount(),
            ],
            'products' => [
                'total' => \App\Models\Product::count(),
                'low_stock' => $this->productService->getLowStockProducts(10)->count(),
            ],
            'orders' => $this->orderService->getOrderStatistics(),
        ];

        $recentOrders = $this->orderService->getAllOrders([], 5)->items();
        $lowStockProducts = $this->productService->getLowStockProducts(10);

        return view('dashboard', compact('statistics', 'recentOrders', 'lowStockProducts'));
    }
}
