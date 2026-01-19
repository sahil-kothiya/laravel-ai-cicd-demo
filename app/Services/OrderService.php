<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected OrderRepository $orderRepository;

    protected ProductRepository $productRepository;

    public function __construct(OrderRepository $orderRepository, ProductRepository $productRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Get paginated orders with optional filters
     */
    public function getAllOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->getAllPaginated($filters, $perPage);
    }

    /**
     * Create a new order
     */
    public function createOrder(array $data): Order
    {
        DB::beginTransaction();
        try {
            // Get product to calculate prices
            $product = $this->productRepository->findById($data['product_id']);

            // Check stock availability
            if ($product->stock < $data['quantity']) {
                throw new \Exception('Insufficient stock available. Only '.$product->stock.' items in stock.');
            }

            // Generate order number
            $data['order_number'] = $this->generateOrderNumber();
            $data['unit_price'] = $product->price;
            $data['total_price'] = $product->price * $data['quantity'];
            $data['ordered_at'] = now();

            // Create order
            $order = $this->orderRepository->create($data);

            // Update product stock
            $this->productRepository->updateStock($data['product_id'], $data['quantity'], 'subtract');

            DB::commit();

            return $this->orderRepository->findById($order->id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get order by ID
     */
    public function getOrderById(int $id): Order
    {
        return $this->orderRepository->findById($id);
    }

    /**
     * Update order
     */
    public function updateOrder(int $id, array $data): Order
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);
            $product = Product::findOrFail($data['product_id']);

            // Calculate stock difference if quantity changed
            if (isset($data['quantity']) && $data['quantity'] != $order->quantity) {
                $difference = $data['quantity'] - $order->quantity;

                if ($difference > 0 && $product->stock < $difference) {
                    throw new \Exception('Insufficient stock for quantity increase.');
                }

                if ($difference > 0) {
                    $product->decrement('stock', $difference);
                } else {
                    $product->increment('stock', abs($difference));
                }
            }

            // Recalculate prices if product or quantity changed
            if (isset($data['product_id']) || isset($data['quantity'])) {
                $data['unit_price'] = $product->price;
                $data['total_price'] = $product->price * ($data['quantity'] ?? $order->quantity);
            }

            // Update processed_at if status changes to completed
            if (isset($data['status']) && $data['status'] === 'completed' && $order->status !== 'completed') {
                $data['processed_at'] = now();
            }

            $order->update($data);

            DB::commit();

            return $order->fresh(['user', 'product']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete order
     */
    public function deleteOrder(int $id): bool
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            // Restore stock if order is not completed
            if ($order->status !== 'completed') {
                $product = Product::find($order->product_id);
                if ($product) {
                    $product->increment('stock', $order->quantity);
                }
            }

            $order->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(int $id, string $status): Order
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            $order->status = $status;

            if ($status === 'completed') {
                $order->processed_at = now();
            }

            $order->save();

            DB::commit();

            return $order->load(['user', 'product']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.strtoupper(Str::random(6)).'-'.now()->format('Ymd');
        } while ($this->orderRepository->orderNumberExists($orderNumber));

        return $orderNumber;
    }

    /**
     * Get order statistics
     */
    public function getOrderStatistics(): array
    {
        return $this->orderRepository->getStatistics();
    }
}
