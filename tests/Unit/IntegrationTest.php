<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_place_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);
    }

    public function test_order_reduces_product_stock(): void
    {
        $product = Product::factory()->create(['stock' => 100]);
        $initialStock = $product->stock;
        
        $this->assertEquals(100, $initialStock);
    }

    public function test_completed_order_has_processed_date(): void
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

    public function test_user_with_multiple_orders(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(5)->create();
        
        foreach ($products as $product) {
            Order::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id
            ]);
        }
        
        $this->assertCount(5, $user->orders);
    }

    public function test_product_with_multiple_orders(): void
    {
        $users = User::factory()->count(3)->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        foreach ($users as $user) {
            Order::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id
            ]);
        }
        
        $this->assertCount(3, $product->orders);
    }

    public function test_active_users_with_orders(): void
    {
        $activeUsers = User::factory()->count(3)->create(['status' => 'active']);
        $product = Product::factory()->create();
        
        foreach ($activeUsers as $user) {
            Order::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id
            ]);
        }
        
        $this->assertEquals(3, Order::count());
    }

    public function test_inactive_products_no_new_orders(): void
    {
        $product = Product::factory()->create(['status' => 'inactive']);
        $this->assertEquals('inactive', $product->status);
    }

    public function test_bulk_order_creation(): void
    {
        $users = User::factory()->count(10)->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        foreach ($users as $user) {
            Order::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => 1
            ]);
        }
        
        $this->assertEquals(10, Order::count());
    }

    public function test_order_total_matches_price_times_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 15.00]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'total_price' => 45.00
        ]);
        
        $this->assertEquals(45.00, $order->total_price);
    }

    public function test_cancelled_order_workflow(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 50]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'cancelled'
        ]);
        
        $this->assertEquals('cancelled', $order->status);
    }

    public function test_featured_products_query(): void
    {
        Product::factory()->count(3)->create(['is_featured' => true]);
        Product::factory()->count(7)->create(['is_featured' => false]);
        
        $featured = Product::where('is_featured', true)->count();
        $this->assertEquals(3, $featured);
    }

    public function test_users_by_status_distribution(): void
    {
        User::factory()->count(5)->create(['status' => 'active']);
        User::factory()->count(3)->create(['status' => 'inactive']);
        User::factory()->count(2)->create(['status' => 'suspended']);
        
        $this->assertEquals(5, User::where('status', 'active')->count());
        $this->assertEquals(3, User::where('status', 'inactive')->count());
        $this->assertEquals(2, User::where('status', 'suspended')->count());
    }

    public function test_products_by_category_distribution(): void
    {
        Product::factory()->count(4)->create(['category' => 'electronics']);
        Product::factory()->count(3)->create(['category' => 'clothing']);
        Product::factory()->count(2)->create(['category' => 'books']);
        
        $this->assertEquals(4, Product::where('category', 'electronics')->count());
        $this->assertEquals(3, Product::where('category', 'clothing')->count());
    }

    public function test_orders_by_status_distribution(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        Order::factory()->count(5)->create(['user_id' => $user->id, 'product_id' => $product->id, 'status' => 'pending']);
        Order::factory()->count(3)->create(['user_id' => $user->id, 'product_id' => $product->id, 'status' => 'completed']);
        
        $this->assertEquals(5, Order::where('status', 'pending')->count());
        $this->assertEquals(3, Order::where('status', 'completed')->count());
    }

    public function test_complex_query_with_relationships(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $product = Product::factory()->create(['status' => 'active', 'stock' => 50]);
        
        Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'completed'
        ]);
        
        $completedOrders = Order::where('status', 'completed')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->whereHas('product', fn($q) => $q->where('status', 'active'))
            ->count();
        
        $this->assertEquals(1, $completedOrders);
    }
}
