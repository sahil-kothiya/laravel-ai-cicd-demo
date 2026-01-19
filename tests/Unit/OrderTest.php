<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'status' => 'pending',
        ]);
    }

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_belongs_to_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(Product::class, $order->product);
        $this->assertEquals($product->id, $order->product->id);
    }

    public function test_order_status_values(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $statuses = ['pending', 'processing', 'completed', 'cancelled'];

        foreach ($statuses as $status) {
            $order = Order::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'status' => $status,
            ]);

            $this->assertEquals($status, $order->status);
        }
    }

    public function test_multiple_orders_for_user(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            Order::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $this->assertCount(3, $user->orders);
    }

    public function test_order_total_calculation(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 100,
            'total_price' => 300,
        ]);

        $this->assertEquals(300, $order->total_price);
    }

    public function test_bulk_order_creation(): void
    {
        $users = User::factory()->count(5)->create();
        $products = Product::factory()->count(5)->create();

        foreach ($users as $user) {
            foreach ($products as $product) {
                Order::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }

        $this->assertDatabaseCount('orders', 25);
    }
}
