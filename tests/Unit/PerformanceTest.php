<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_large_user_dataset_creation(): void
    {
        $startTime = microtime(true);
        
        User::factory()->count(100)->create();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $this->assertDatabaseCount('users', 100);
        $this->assertLessThan(10, $executionTime);
    }

    public function test_large_product_catalog_creation(): void
    {
        $startTime = microtime(true);
        
        $categories = ['Electronics', 'Clothing', 'Books'];
        
        foreach ($categories as $category) {
            Product::factory()->count(30)->create([
                'category' => $category
            ]);
        }
        
        $endTime = microtime(true);
        
        $this->assertDatabaseCount('products', 90);
        $this->assertLessThan(10, $endTime - $startTime);
    }

    public function test_bulk_order_creation_performance(): void
    {
        $users = User::factory()->count(20)->create();
        $products = Product::factory()->count(20)->create();
        
        $startTime = microtime(true);
        
        foreach ($users as $user) {
            $randomProducts = $products->random(3);
            
            foreach ($randomProducts as $product) {
                Order::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 5)
                ]);
            }
        }
        
        $endTime = microtime(true);
        
        $this->assertDatabaseCount('orders', 60);
        $this->assertLessThan(15, $endTime - $startTime);
    }

    public function test_complex_relationships_query(): void
    {
        $users = User::factory()->count(10)->create();
        $products = Product::factory()->count(10)->create();
        
        foreach ($users as $user) {
            foreach ($products->random(2) as $product) {
                Order::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id
                ]);
            }
        }
        
        $startTime = microtime(true);
        
        $usersWithOrders = User::with('orders.product')->get();
        
        $endTime = microtime(true);
        
        $this->assertGreaterThan(0, $usersWithOrders->count());
        $this->assertLessThan(2, $endTime - $startTime);
    }

    public function test_database_count_verification(): void
    {
        User::factory()->count(50)->create();
        Product::factory()->count(100)->create();
        
        $this->assertEquals(50, User::count());
        $this->assertEquals(100, Product::count());
    }

    public function test_category_based_product_distribution(): void
    {
        $categories = ['Electronics', 'Clothing', 'Books', 'Sports'];
        
        foreach ($categories as $category) {
            Product::factory()->count(25)->create(['category' => $category]);
        }
        
        foreach ($categories as $category) {
            $count = Product::where('category', $category)->count();
            $this->assertEquals(25, $count);
        }
    }

    public function test_status_based_order_distribution(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];
        
        foreach ($statuses as $status) {
            Order::factory()->count(10)->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'status' => $status
            ]);
        }
        
        foreach ($statuses as $status) {
            $count = Order::where('status', $status)->count();
            $this->assertEquals(10, $count);
        }
    }

    public function test_memory_efficient_batch_processing(): void
    {
        $startMemory = memory_get_usage();
        
        User::factory()->count(200)->create();
        
        $endMemory = memory_get_usage();
        $memoryUsed = $endMemory - $startMemory;
        
        $this->assertDatabaseCount('users', 200);
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed); // Less than 50MB
    }
}
