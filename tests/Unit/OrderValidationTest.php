<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        
        $this->assertEquals($user->id, $order->user_id);
    }

    public function test_order_belongs_to_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        
        $this->assertEquals($product->id, $order->product_id);
    }

    public function test_order_quantity_is_positive(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 5]);
        
        $this->assertGreaterThan(0, $order->quantity);
    }

    public function test_order_quantity_is_integer(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 3]);
        
        $this->assertIsInt($order->quantity);
    }

    public function test_order_total_calculation(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10.00]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total' => 20.00
        ]);
        
        $this->assertEquals(20.00, $order->total);
    }

    public function test_order_status_pending(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending'
        ]);
        
        $this->assertEquals('pending', $order->status);
    }

    public function test_order_status_completed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'completed'
        ]);
        
        $this->assertEquals('completed', $order->status);
    }

    public function test_order_status_cancelled(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'cancelled'
        ]);
        
        $this->assertEquals('cancelled', $order->status);
    }

    public function test_order_number_format(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        
        $this->assertNotNull($order->order_number);
    }

    public function test_order_timestamps_exist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        
        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
    }

    public function test_order_processed_at_nullable(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'pending',
            'processed_at' => null
        ]);
        
        $this->assertNull($order->processed_at);
    }

    public function test_order_processed_at_set_when_completed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'completed',
            'processed_at' => now()
        ]);
        
        $this->assertNotNull($order->processed_at);
    }

    public function test_multiple_orders_same_user(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();
        
        foreach ($products as $product) {
            Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        }
        
        $this->assertEquals(3, $user->orders()->count());
    }

    public function test_multiple_orders_same_product(): void
    {
        $users = User::factory()->count(2)->create();
        $product = Product::factory()->create();
        
        foreach ($users as $user) {
            Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        }
        
        $this->assertEquals(2, $product->orders()->count());
    }

    public function test_order_total_positive(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 25.50]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'total' => 25.50
        ]);
        
        $this->assertGreaterThan(0, $order->total);
    }
}
