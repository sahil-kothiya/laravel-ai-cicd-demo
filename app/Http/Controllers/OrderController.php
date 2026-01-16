<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource with filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $userId = $request->input('user_id');
            $status = $request->input('status');
            
            if ($perPage > 100) {
                return response()->json(['error' => 'Maximum per_page value is 100'], 422);
            }
            
            $cacheKey = "orders_list_{$perPage}_{$userId}_{$status}";
            
            $orders = Cache::remember($cacheKey, 300, function () use ($perPage, $userId, $status) {
                $query = Order::with(['user', 'product']);
                
                if ($userId) {
                    $query->where('user_id', $userId);
                }
                
                if ($status) {
                    $query->where('status', $status);
                }
                
                return $query->orderBy('ordered_at', 'desc')->paginate($perPage);
            });
            
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching orders'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:1000',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Check product availability
            $product = Product::lockForUpdate()->find($validated['product_id']);
            
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }
            
            if ($product->status !== 'active') {
                return response()->json(['error' => 'Product is not available'], 422);
            }
            
            if ($product->stock < $validated['quantity']) {
                return response()->json([
                    'error' => 'Insufficient stock. Available: ' . $product->stock
                ], 422);
            }
            
            // Calculate prices
            $unitPrice = $product->price;
            $totalPrice = $unitPrice * $validated['quantity'];
            
            // Create order
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'product_id' => $validated['product_id'],
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'quantity' => $validated['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'ordered_at' => now(),
            ]);
            
            // Update product stock
            $product->decrement('stock', $validated['quantity']);
            
            Cache::tags(['orders', 'products'])->flush();
            
            DB::commit();
            
            Log::info('Order created successfully', ['order_id' => $order->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['user', 'product'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while creating the order'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return response()->json(['error' => 'Invalid order ID'], 422);
            }
            
            $cacheKey = "order_{$id}";
            
            $order = Cache::remember($cacheKey, 300, function () use ($id) {
                return Order::with(['user', 'product'])->find($id);
            });
            
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching order: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching the order'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'Invalid order ID'], 422);
        }
        
        $order = Order::find($id);
        
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        
        $validated = $request->validate([
            'status' => 'sometimes|required|string|in:pending,processing,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            DB::beginTransaction();
            
            // If status is being changed to completed, record processed_at
            if (isset($validated['status']) && $validated['status'] === 'completed' && $order->status !== 'completed') {
                $validated['processed_at'] = now();
            }
            
            // If order is being cancelled, restore product stock
            if (isset($validated['status']) && $validated['status'] === 'cancelled' && $order->status !== 'cancelled') {
                $product = Product::find($order->product_id);
                if ($product) {
                    $product->increment('stock', $order->quantity);
                }
            }
            
            $order->update($validated);
            
            Cache::forget("order_{$id}");
            Cache::tags(['orders'])->flush();
            
            DB::commit();
            
            Log::info('Order updated successfully', ['order_id' => $order->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order->fresh()->load(['user', 'product'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while updating the order'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return response()->json(['error' => 'Invalid order ID'], 422);
            }
            
            DB::beginTransaction();
            
            $order = Order::find($id);
            
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            // Only allow deletion of pending orders
            if ($order->status !== 'pending') {
                return response()->json([
                    'error' => 'Only pending orders can be deleted'
                ], 422);
            }
            
            // Restore product stock
            $product = Product::find($order->product_id);
            if ($product) {
                $product->increment('stock', $order->quantity);
            }
            
            $order->delete();
            
            Cache::forget("order_{$id}");
            Cache::tags(['orders'])->flush();
            
            DB::commit();
            
            Log::info('Order deleted successfully', ['order_id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting order: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while deleting the order'], 500);
        }
    }
}
