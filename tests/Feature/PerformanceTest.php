<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test large dataset creation
     * This test creates massive amounts of data to simulate real-world load
     * Traditional CI/CD: ~15-20 seconds
     * AI Optimized: Can skip if no changes in models/factories
     */
    public function test_massive_user_creation_performance(): void
    {
        $startTime = microtime(true);
        
        // Create 200 users
        User::factory()->count(200)->create();
        
        sleep(3); // Simulate heavy database operations
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $this->assertDatabaseCount('users', 200);
        $this->assertGreaterThan(0, $executionTime);
    }

    /**
     * Test large product catalog creation
     * Traditional CI/CD: ~12-15 seconds
     * AI Optimized: Skip if product module unchanged
     */
    public function test_massive_product_catalog_creation(): void
    {
        $startTime = microtime(true);
        
        // Create 300 products across different categories
        $categories = ['Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports', 'Toys'];
        
        foreach ($categories as $category) {
            Product::factory()->count(50)->create([
                'category' => $category
            ]);
        }
        
        sleep(3); // Simulate heavy processing
        
        $endTime = microtime(true);
        
        $this->assertDatabaseCount('products', 300);
        
        foreach ($categories as $category) {
            $count = Product::where('category', $category)->count();
            $this->assertEquals(50, $count);
        }
    }

    /**
     * Test complex order processing with relationships
     * Traditional CI/CD: ~18-22 seconds
     * AI Optimized: Run only if order logic changed
     */
    public function test_massive_order_processing_with_relationships(): void
    {
        $startTime = microtime(true);
        
        // Setup: Create users and products
        $users = User::factory()->count(50)->create();
        $products = Product::factory()->count(100)->create([
            'stock' => 1000,
            'status' => 'active'
        ]);
        
        sleep(2); // Simulate setup time
        
        // Create orders for each user
        foreach ($users as $user) {
            $randomProducts = $products->random(5);
            
            foreach ($randomProducts as $product) {
                Order::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 5)
                ]);
            }
        }
        
        sleep(4); // Simulate heavy order processing
        
        $endTime = microtime(true);
        
        // Verify data integrity
        $this->assertDatabaseCount('orders', 250); // 50 users * 5 products
        
        // Verify relationships work correctly
        $firstUser = $users->first();
        $this->assertEquals(5, $firstUser->orders()->count());
    }

    /**
     * Test complex queries with multiple joins and aggregations
     * Traditional CI/CD: ~10-12 seconds
     * AI Optimized: Skip if query logic unchanged
     */
    public function test_complex_reporting_queries_performance(): void
    {
        // Setup data
        $users = User::factory()->count(30)->create();
        $products = Product::factory()->count(50)->create();
        
        foreach ($users as $user) {
            Order::factory()->count(rand(3, 10))->create([
                'user_id' => $user->id,
                'product_id' => $products->random()->id
            ]);
        }
        
        sleep(2); // Simulate query execution time
        
        // Complex query with joins
        $results = Order::with(['user', 'product'])
                       ->where('status', 'completed')
                       ->get();
        
        sleep(1); // Simulate additional processing
        
        $this->assertGreaterThan(0, $results->count());
    }

    /**
     * Test validation on large batch operations
     * Traditional CI/CD: ~8-10 seconds
     * AI Optimized: Run only if validation logic changed
     */
    public function test_bulk_validation_performance(): void
    {
        $users = [];
        
        // Prepare 100 users for bulk creation
        for ($i = 0; $i < 100; $i++) {
            $users[] = [
                'name' => "Test User {$i}",
                'email' => "testuser{$i}@example.com",
                'password' => 'SecurePassword123!@#'
            ];
        }
        
        sleep(2); // Simulate validation time
        
        $response = $this->postJson('/users/bulk', ['users' => $users]);
        
        $response->assertStatus(201);
        
        sleep(1); // Simulate post-processing
        
        $this->assertDatabaseCount('users', 100);
    }

    /**
     * Test API stress with multiple concurrent requests simulation
     * Traditional CI/CD: ~15-18 seconds
     * AI Optimized: Skip if API controllers unchanged
     */
    public function test_api_stress_test_multiple_endpoints(): void
    {
        $users = User::factory()->count(20)->create();
        $products = Product::factory()->count(30)->create();
        
        sleep(1);
        
        // Simulate multiple API calls
        foreach ($users as $user) {
            $this->getJson("/users/{$user->id}")->assertStatus(200);
        }
        
        sleep(2);
        
        foreach ($products->take(10) as $product) {
            $this->getJson("/products/{$product->id}")->assertStatus(200);
        }
        
        sleep(1);
        
        $this->assertTrue(true);
    }

    /**
     * Test cache performance with heavy queries
     * Traditional CI/CD: ~12-14 seconds
     * AI Optimized: Skip if caching logic unchanged
     */
    public function test_caching_performance_with_heavy_queries(): void
    {
        Product::factory()->count(100)->create();
        
        sleep(2); // First query without cache
        
        // First call - should cache
        $response1 = $this->getJson('/products?per_page=50');
        $response1->assertStatus(200);
        
        sleep(1);
        
        // Second call - should hit cache
        $response2 = $this->getJson('/products?per_page=50');
        $response2->assertStatus(200);
        
        sleep(1);
        
        $this->assertEquals(
            $response1->json('data.total'),
            $response2->json('data.total')
        );
    }

    /**
     * Test database transaction rollback performance
     * Traditional CI/CD: ~6-8 seconds
     * AI Optimized: Run only if transaction logic changed
     */
    public function test_transaction_rollback_performance(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock' => 5,
            'status' => 'active'
        ]);
        
        sleep(1);
        
        // Try to create order with insufficient stock
        $response = $this->postJson('/orders', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 10
        ]);
        
        sleep(1);
        
        $response->assertStatus(422);
        
        // Verify no order was created
        $this->assertDatabaseCount('orders', 0);
        
        // Verify stock wasn't modified
        $product->refresh();
        $this->assertEquals(5, $product->stock);
    }

    /**
     * Test soft delete operations at scale
     * Traditional CI/CD: ~7-9 seconds
     * AI Optimized: Skip if delete logic unchanged
     */
    public function test_soft_delete_operations_at_scale(): void
    {
        $products = Product::factory()->count(50)->create();
        
        sleep(2);
        
        // Soft delete half of them
        foreach ($products->take(25) as $product) {
            $product->delete();
        }
        
        sleep(2);
        
        // Verify soft deletes
        $this->assertEquals(25, Product::count());
        $this->assertEquals(50, Product::withTrashed()->count());
        
        sleep(1);
    }

    /**
     * Test full text search simulation
     * Traditional CI/CD: ~10-12 seconds
     * AI Optimized: Skip if search logic unchanged
     */
    public function test_full_text_search_simulation(): void
    {
        // Create products with specific searchable content
        Product::factory()->count(50)->create();
        Product::factory()->create([
            'name' => 'Super Special Unique Product',
            'description' => 'This is a very unique product description',
            'sku' => 'SSU12345'
        ]);
        
        sleep(3); // Simulate heavy search indexing
        
        $response = $this->getJson('/products?search=Super Special Unique');
        
        sleep(1);
        
        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertGreaterThan(0, count($data));
    }

    /**
     * Test data integrity across multiple operations
     * Traditional CI/CD: ~14-16 seconds
     * AI Optimized: Run only if core business logic changed
     */
    public function test_data_integrity_across_operations(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock' => 100,
            'price' => 50.00,
            'status' => 'active'
        ]);
        
        sleep(1);
        
        // Create multiple orders
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/orders', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => 5
            ])->assertStatus(201);
        }
        
        sleep(2);
        
        // Verify stock decreased correctly
        $product->refresh();
        $this->assertEquals(50, $product->stock);
        
        sleep(1);
        
        // Verify all orders were created
        $this->assertEquals(10, $user->orders()->count());
        
        // Verify total price calculations
        $totalOrderValue = $user->orders()->sum('total_price');
        $this->assertEquals(2500.00, $totalOrderValue); // 10 orders * 5 qty * $50
    }

    /**
     * Test memory intensive operations
     * Traditional CI/CD: ~12-15 seconds
     * AI Optimized: Skip if optimization unchanged
     */
    public function test_memory_intensive_operations(): void
    {
        $users = User::factory()->count(100)->create();
        $products = Product::factory()->count(100)->create();
        
        sleep(3); // Simulate heavy memory usage
        
        // Process all combinations (would be heavy in real scenario)
        $processed = 0;
        foreach ($users->take(10) as $user) {
            foreach ($products->take(10) as $product) {
                $processed++;
            }
        }
        
        sleep(2);
        
        $this->assertEquals(100, $processed);
    }
}
