<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    protected Order $model;

    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    /**
     * Get all orders with pagination and filters
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'product']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('order_number', 'LIKE', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get all orders without pagination
     */
    public function getAll(): Collection
    {
        return $this->model->with(['user', 'product'])->get();
    }

    /**
     * Find order by ID
     */
    public function findById(int $id): ?Order
    {
        return $this->model->with(['user', 'product'])->findOrFail($id);
    }

    /**
     * Create new order
     */
    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    /**
     * Update order
     */
    public function update(int $id, array $data): Order
    {
        $order = $this->model->findOrFail($id);
        $order->update($data);
        return $order->fresh(['user', 'product']);
    }

    /**
     * Delete order
     */
    public function delete(int $id): bool
    {
        $order = $this->findById($id);
        return $order->delete();
    }

    /**
     * Get orders by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Count orders by status
     */
    public function countByStatus(string $status): int
    {
        return $this->model->where('status', $status)->count();
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue(): float
    {
        return $this->model->where('status', 'completed')->sum('total_price');
    }

    /**
     * Get order statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'pending' => $this->countByStatus('pending'),
            'processing' => $this->countByStatus('processing'),
            'completed' => $this->countByStatus('completed'),
            'cancelled' => $this->countByStatus('cancelled'),
            'total_revenue' => $this->getTotalRevenue(),
        ];
    }

    /**
     * Check if order number exists
     */
    public function orderNumberExists(string $orderNumber): bool
    {
        return $this->model->where('order_number', $orderNumber)->exists();
    }
}
