<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test order index with pagination
     */
    public function test_index_returns_paginated_orders(): void
    {
        $users = User::factory()->count(10)->create();
        $products = Product::factory()->count(10)->create();
        
        foreach ($users as $user) {
            Order::factory()->count(5)->create([
                'user_id' => $user->id,
                'product_id' => $products->random()->id
            ]);
        }
        
        sleep(1); // Simulate slow query with relationships
        
        $response = $this->getJson('/orders?per_page=20');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'current_page',
                         'data',
                         'per_page',
                         'total'
                     ]
                 ]);
    }

    /**
     * Test order index filtered by user
     */
    public function test_index_filtered_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();
        
        Order::factory()->count(5)->create([
            'user_id' => $user1->id,
            'product_id' => $product->id
        ]);
        
        Order::factory()->count(3)->create([
            'user_id' => $user2->id,
            'product_id' => $product->id
        ]);
        
        sleep(1); // Simulate filtering
        
        $response = $this->getJson("/orders?user_id={$user1->id}");
        
        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
    }

    /**
     * Test order index filtered by status
     */
    public function test_index_filtered_by_status(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        Order::factory()->count(7)->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'completed'
        ]);
        
        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending'
        ]);
        
        $response = $this->getJson('/orders?status=completed');
        
        $response->assertStatus(200);
        $this->assertCount(7, $response->json('data.data'));
    }

    /**
     * Test order creation with valid data
     */
    public function test_store_creates_order_with_valid_data(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100.00,
            'stock' => 50,
            'status' => 'active'
        ]);
        
        $orderData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'notes' => 'Test order'
        ];
        
        sleep(1); // Simulate order processing
        
        $response = $this->postJson('/orders', $orderData);
        
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'order_number',
                         'quantity',
                         'unit_price',
                         'total_price'
                     ]
                 ]);
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3
        ]);
        
        // Verify stock was decremented
        $product->refresh();
        $this->assertEquals(47, $product->stock);
    }

    /**
     * Test order creation validation - user exists
     */
    public function test_store_validation_user_exists(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        
        $orderData = [
            'user_id' => 99999,
            'product_id' => $product->id,
            'quantity' => 1
        ];
        
        $response = $this->postJson('/orders', $orderData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['user_id']);
    }

    /**
     * Test order creation validation - product exists
     */
    public function test_store_validation_product_exists(): void
    {
        $user = User::factory()->create();
        
        $orderData = [
            'user_id' => $user->id,
            'product_id' => 99999,
            'quantity' => 1
        ];
        
        $response = $this->postJson('/orders', $orderData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Test order creation validation - quantity required
     */
    public function test_store_validation_quantity_required(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        
        $orderData = [
            'user_id' => $user->id,
            'product_id' => $product->id
        ];
        
        $response = $this->postJson('/orders', $orderData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['quantity']);
    }

    /**
     * Test order creation - insufficient stock
     */
    public function test_store_rejects_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock' => 5,
            'status' => 'active'
        ]);
        
        $orderData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 10
        ];
        
        $response = $this->postJson('/orders', $orderData);
        
        $response->assertStatus(422)
                 ->assertJson([
                     'error' => 'Insufficient stock. Available: 5'
                 ]);
    }

    /**
     * Test order creation - inactive product
     */
    public function test_store_rejects_inactive_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock' => 50,
            'status' => 'inactive'
        ]);
        
        $orderData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1
        ];
        
        $response = $this->postJson('/orders', $orderData);
        
        $response->assertStatus(422)
                 ->assertJson([
                     'error' => 'Product is not available'
                 ]);
    }

    /**
     * Test order show with relationships
     */
    public function test_show_returns_order_with_relationships(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);
        
        $response = $this->getJson("/orders/{$order->id}");
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'order_number',
                         'user',
                         'product'
                     ]
                 ]);
    }

    /**
     * Test order update - change status
     */
    public function test_update_changes_order_status(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending'
        ]);
        
        $updateData = ['status' => 'processing'];
        
        $response = $this->putJson("/orders/{$order->id}", $updateData);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing'
        ]);
    }

    /**
     * Test order update - mark as completed
     */
    public function test_update_marks_order_as_completed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'processing',
            'processed_at' => null
        ]);
        
        sleep(1); // Simulate processing
        
        $updateData = ['status' => 'completed'];
        
        $response = $this->putJson("/orders/{$order->id}", $updateData);
        
        $response->assertStatus(200);
        
        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertNotNull($order->processed_at);
    }

    /**
     * Test order update - cancel order restores stock
     */
    public function test_update_cancel_order_restores_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'status' => 'pending'
        ]);
        
        // Manually adjust stock as if order was placed
        $product->update(['stock' => 90]);
        
        $updateData = ['status' => 'cancelled'];
        
        $response = $this->putJson("/orders/{$order->id}", $updateData);
        
        $response->assertStatus(200);
        
        $product->refresh();
        $this->assertEquals(100, $product->stock);
    }

    /**
     * Test order deletion
     */
    public function test_destroy_deletes_pending_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'status' => 'pending'
        ]);
        
        // Manually adjust stock
        $product->update(['stock' => 95]);
        
        $response = $this->deleteJson("/orders/{$order->id}");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Order deleted successfully'
                 ]);
        
        // Verify stock was restored
        $product->refresh();
        $this->assertEquals(100, $product->stock);
    }

    /**
     * Test order deletion - cannot delete non-pending
     */
    public function test_destroy_prevents_deletion_of_completed_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'completed'
        ]);
        
        $response = $this->deleteJson("/orders/{$order->id}");
        
        $response->assertStatus(422)
                 ->assertJson([
                     'error' => 'Only pending orders can be deleted'
                 ]);
    }

    /**
     * Test concurrent order creation
     */
    public function test_concurrent_order_creation_with_stock_locking(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock' => 10,
            'status' => 'active'
        ]);
        
        // Simulate concurrent order attempts
        for ($i = 0; $i < 5; $i++) {
            $orderData = [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => 2
            ];
            
            $this->postJson('/orders', $orderData);
        }
        
        sleep(2); // Simulate processing time
        
        $product->refresh();
        $this->assertEquals(0, $product->stock);
    }

    /**
     * Test order scopes - pending
     */
    public function test_pending_scope_filters_correctly(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        Order::factory()->count(8)->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending'
        ]);
        
        Order::factory()->count(4)->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'completed'
        ]);
        
        $pendingOrders = Order::pending()->get();
        
        $this->assertCount(8, $pendingOrders);
    }

    /**
     * Test order scopes - completed
     */
    public function test_completed_scope_filters_correctly(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        Order::factory()->count(6)->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'completed'
        ]);
        
        Order::factory()->count(4)->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending'
        ]);
        
        sleep(1); // Simulate query
        
        $completedOrders = Order::completed()->get();
        
        $this->assertCount(6, $completedOrders);
    }

    /**
     * Test bulk order processing
     */
    public function test_bulk_order_processing_performance(): void
    {
        $users = User::factory()->count(10)->create();
        $products = Product::factory()->count(5)->create([
            'stock' => 100,
            'status' => 'active'
        ]);
        
        foreach ($users as $user) {
            foreach ($products as $product) {
                Order::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id
                ]);
            }
        }
        
        sleep(2); // Simulate heavy processing
        
        $this->assertDatabaseCount('orders', 50);
    }

    /**
     * Test complex order reporting query
     */
    public function test_complex_order_reporting_with_aggregates(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();
        
        foreach ($products as $product) {
            Order::factory()->count(10)->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'status' => 'completed'
            ]);
        }
        
        sleep(2); // Simulate complex aggregation query
        
        $totalOrders = Order::where('user_id', $user->id)->count();
        $completedOrders = Order::where('user_id', $user->id)
                                ->where('status', 'completed')
                                ->count();
        
        $this->assertEquals(30, $totalOrders);
        $this->assertEquals(30, $completedOrders);
    }
}
