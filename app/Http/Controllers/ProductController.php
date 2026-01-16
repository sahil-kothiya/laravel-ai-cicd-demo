<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $category = $request->input('category');
            $status = $request->input('status');

            if ($perPage > 100) {
                return response()->json(['error' => 'Maximum per_page value is 100'], 422);
            }

            $cacheKey = "products_list_{$perPage}_{$search}_{$category}_{$status}";

            $products = Cache::remember($cacheKey, 300, function () use ($perPage, $search, $category, $status) {
                $query = Product::query();

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                }

                if ($category) {
                    $query->where('category', $category);
                }

                if ($status) {
                    $query->where('status', $status);
                }

                return $query->orderBy('created_at', 'desc')->paginate($perPage);
            });

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching products: '.$e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching products'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'sku' => 'required|string|unique:products,sku|regex:/^[A-Z]{3}[0-9]{5}$/',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'stock' => 'required|integer|min:0|max:99999',
            'category' => 'nullable|string|in:Electronics,Clothing,Books,Home & Garden,Sports,Toys',
            'status' => 'sometimes|string|in:active,inactive,discontinued',
            'is_featured' => 'sometimes|boolean',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::create($validated);

            Cache::tags(['products'])->flush();

            DB::commit();

            Log::info('Product created successfully', ['product_id' => $product->id]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating product: '.$e->getMessage());

            return response()->json(['error' => 'An error occurred while creating the product'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            if (! is_numeric($id) || $id <= 0) {
                return response()->json(['error' => 'Invalid product ID'], 422);
            }

            $cacheKey = "product_{$id}";

            $product = Cache::remember($cacheKey, 300, function () use ($id) {
                return Product::with('orders')->find($id);
            });

            if (! $product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching product: '.$e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching the product'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if (! is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'Invalid product ID'], 422);
        }

        $product = Product::find($id);

        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'sku' => [
                'sometimes',
                'required',
                'string',
                'regex:/^[A-Z]{3}[0-9]{5}$/',
                Rule::unique('products')->ignore($id),
            ],
            'price' => 'sometimes|required|numeric|min:0.01|max:999999.99',
            'stock' => 'sometimes|required|integer|min:0|max:99999',
            'category' => 'nullable|string|in:Electronics,Clothing,Books,Home & Garden,Sports,Toys',
            'status' => 'sometimes|string|in:active,inactive,discontinued',
            'is_featured' => 'sometimes|boolean',
        ]);

        try {
            DB::beginTransaction();

            $product->update($validated);

            Cache::forget("product_{$id}");
            Cache::tags(['products'])->flush();

            DB::commit();

            Log::info('Product updated successfully', ['product_id' => $product->id]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product: '.$e->getMessage());

            return response()->json(['error' => 'An error occurred while updating the product'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            if (! is_numeric($id) || $id <= 0) {
                return response()->json(['error' => 'Invalid product ID'], 422);
            }

            DB::beginTransaction();

            $product = Product::find($id);

            if (! $product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            // Check if product has orders
            if ($product->orders()->count() > 0) {
                return response()->json([
                    'error' => 'Cannot delete product with existing orders',
                ], 422);
            }

            $product->delete();

            Cache::forget("product_{$id}");
            Cache::tags(['products'])->flush();

            DB::commit();

            Log::info('Product deleted successfully', ['product_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting product: '.$e->getMessage());

            return response()->json(['error' => 'An error occurred while deleting the product'], 500);
        }
    }
}
