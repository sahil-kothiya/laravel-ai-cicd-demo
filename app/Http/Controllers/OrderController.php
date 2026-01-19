<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Services\OrderService;
use App\Services\UserService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected UserService $userService;
    protected ProductService $productService;

    public function __construct(
        OrderService $orderService,
        UserService $userService,
        ProductService $productService
    ) {
        $this->orderService = $orderService;
        $this->userService = $userService;
        $this->productService = $productService;
    }

    /**
     * Display a listing of the orders.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->get('search'),
            'user_id' => $request->get('user_id'),
            'product_id' => $request->get('product_id'),
            'status' => $request->get('status'),
        ];

        $orders = $this->orderService->getAllOrders($filters, $request->get('per_page', 15));
        $statistics = $this->orderService->getOrderStatistics();

        return view('orders.index', compact('orders', 'statistics'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create(): View
    {
        $users = $this->userService->getAllUsers([], 1000)->items();
        $products = $this->productService->getAllProducts(['status' => 'active'], 1000)->items();

        return view('orders.create', compact('users', 'products'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        try {
            $this->orderService->createOrder($request->validated());

            return redirect()->route('orders.index')
                ->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating order: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): View
    {
        $order = $this->orderService->getOrderById($id);

        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(int $id): View
    {
        $order = $this->orderService->getOrderById($id);
        $users = $this->userService->getAllUsers([], 1000)->items();
        $products = $this->productService->getAllProducts(['status' => 'active'], 1000)->items();

        return view('orders.edit', compact('order', 'users', 'products'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(UpdateOrderRequest $request, int $id): RedirectResponse
    {
        try {
            $this->orderService->updateOrder($id, $request->validated());

            return redirect()->route('orders.index')
                ->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating order: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->orderService->deleteOrder($id);

            return redirect()->route('orders.index')
                ->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting order: ' . $e->getMessage());
        }
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        try {
            $this->orderService->updateOrderStatus($id, $request->status);

            return redirect()->route('orders.index')
                ->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating order status: ' . $e->getMessage());
        }
    }
}
