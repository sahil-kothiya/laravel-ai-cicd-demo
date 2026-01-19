<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    protected Product $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Get all products with pagination and filters
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('sku', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('description', 'LIKE', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get all products without pagination
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find product by ID
     */
    public function findById(int $id): ?Product
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create new product
     */
    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    /**
     * Update product
     */
    public function update(int $id, array $data): Product
    {
        $product = $this->findById($id);
        $product->update($data);

        return $product->fresh();
    }

    /**
     * Delete product
     */
    public function delete(int $id): bool
    {
        $product = $this->findById($id);

        if ($product->orders()->count() > 0) {
            throw new \Exception('Cannot delete product with existing orders.');
        }

        return $product->delete();
    }

    /**
     * Get low stock products
     */
    public function getLowStock(int $threshold = 10): Collection
    {
        return $this->model->where('stock', '<=', $threshold)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Get all unique categories
     */
    public function getAllCategories(): array
    {
        return $this->model->distinct('category')
            ->pluck('category')
            ->toArray();
    }

    /**
     * Update product stock
     */
    public function updateStock(int $id, int $quantity, string $operation = 'add'): Product
    {
        $product = $this->findById($id);

        if ($operation === 'add') {
            $product->stock += $quantity;
        } else {
            if ($product->stock < $quantity) {
                throw new \Exception('Insufficient stock available.');
            }
            $product->stock -= $quantity;
        }

        $product->save();

        return $product;
    }
}
