<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get paginated products with optional filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getAllPaginated($filters, $perPage);
    }

    /**
     * Create a new product
     *
     * @param array $data
     * @return Product
     */
    public function createProduct(array $data): Product
    {
        DB::beginTransaction();
        try {
            $product = Product::create($data);
            
            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get product by ID
     *
     * @param int $id
     * @return Product
     */
    public function getProductById(int $id): Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Update product
     *
     * @param int $id
     * @param array $data
     * @return Product
     */
    public function updateProduct(int $id, array $data): Product
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepository->update($id, $data);
            
            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete product
     *
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool
    {
        DB::beginTransaction();
        try {
            $result = $this->productRepository->delete($id);
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get low stock products
     *
     * @param int $threshold
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockProducts(int $threshold = 10)
    {
        return $this->productRepository->getLowStock($threshold);
    }

    /**
     * Update product stock
     *
     * @param int $id
     * @param int $quantity
     * @param string $operation
     * @return Product
     */
    public function updateStock(int $id, int $quantity, string $operation = 'add'): Product
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepository->updateStock($id, $quantity, $operation);
            
            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all unique categories
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        return $this->productRepository->getAllCategories();
    }
}
