<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test product index with many records
     * Simulates heavy database load
     */
    public function test_index_returns_paginated_products(): void
    {
        Product::factory()->count(60)->create();

        sleep(1); // Simulate slow query

        $response = $this->getJson('/products?per_page=25');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_page',
                    'data',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertEquals(25, count($response->json('data.data')));
    }

    /**
     * Test product index with search
     */
    public function test_index_with_search_filter(): void
    {
        Product::factory()->create(['name' => 'Smartphone Pro', 'sku' => 'SPH00001']);
        Product::factory()->create(['name' => 'Laptop Ultra', 'sku' => 'LPT00001']);
        Product::factory()->count(40)->create();

        sleep(1); // Simulate slow search

        $response = $this->getJson('/products?search=Smartphone');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertGreaterThan(0, count($data));
    }

    /**
     * Test product index with category filter
     */
    public function test_index_with_category_filter(): void
    {
        Product::factory()->count(15)->create(['category' => 'Electronics']);
        Product::factory()->count(10)->create(['category' => 'Books']);

        $response = $this->getJson('/products?category=Electronics');

        $response->assertStatus(200);
        $this->assertCount(15, $response->json('data.data'));
    }

    /**
     * Test product creation with valid data
     */
    public function test_store_creates_product_with_valid_data(): void
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product description',
            'sku' => 'TST12345',
            'price' => 99.99,
            'stock' => 100,
            'category' => 'Electronics',
            'status' => 'active',
        ];

        $response = $this->postJson('/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'sku',
                    'price',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'TST12345',
            'name' => 'Test Product',
        ]);
    }

    /**
     * Test product creation validation - name required
     */
    public function test_store_validation_name_required(): void
    {
        $productData = [
            'sku' => 'TST12345',
            'price' => 99.99,
            'stock' => 100,
        ];

        $response = $this->postJson('/products', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test product creation validation - SKU format
     */
    public function test_store_validation_sku_format(): void
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'invalid-sku',
            'price' => 99.99,
            'stock' => 100,
        ];

        $response = $this->postJson('/products', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    /**
     * Test product creation validation - SKU unique
     */
    public function test_store_validation_sku_unique(): void
    {
        Product::factory()->create(['sku' => 'DUP12345']);

        $productData = [
            'name' => 'Test Product',
            'sku' => 'DUP12345',
            'price' => 99.99,
            'stock' => 100,
        ];

        $response = $this->postJson('/products', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    /**
     * Test product creation validation - price minimum
     */
    public function test_store_validation_price_minimum(): void
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'TST12345',
            'price' => 0,
            'stock' => 100,
        ];

        $response = $this->postJson('/products', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /**
     * Test product creation validation - stock minimum
     */
    public function test_store_validation_stock_minimum(): void
    {
        $productData = [
            'name' => 'Test Product',
            'sku' => 'TST12345',
            'price' => 99.99,
            'stock' => -1,
        ];

        $response = $this->postJson('/products', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock']);
    }

    /**
     * Test product show
     */
    public function test_show_returns_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'sku',
                    'price',
                    'stock',
                ],
            ]);
    }

    /**
     * Test product show with relationships
     */
    public function test_show_includes_orders_relationship(): void
    {
        $product = Product::factory()->create();
        Order::factory()->count(3)->create(['product_id' => $product->id]);

        sleep(1); // Simulate relationship loading

        $response = $this->getJson("/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'orders',
                ],
            ]);
    }

    /**
     * Test product update
     */
    public function test_update_modifies_product(): void
    {
        $product = Product::factory()->create(['price' => 100.00]);

        $updateData = [
            'price' => 150.00,
            'stock' => 200,
        ];

        $response = $this->putJson("/products/{$product->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'price' => 150.00,
            'stock' => 200,
        ]);
    }

    /**
     * Test product update SKU uniqueness
     */
    public function test_update_validates_sku_uniqueness(): void
    {
        $product1 = Product::factory()->create(['sku' => 'SKU00001']);
        $product2 = Product::factory()->create(['sku' => 'SKU00002']);

        $updateData = ['sku' => 'SKU00002'];

        $response = $this->putJson("/products/{$product1->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    /**
     * Test product deletion
     */
    public function test_destroy_deletes_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
    }

    /**
     * Test product deletion with existing orders
     */
    public function test_destroy_prevents_deletion_with_orders(): void
    {
        $product = Product::factory()->create();
        Order::factory()->create(['product_id' => $product->id]);

        $response = $this->deleteJson("/products/{$product->id}");

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Cannot delete product with existing orders',
            ]);
    }

    /**
     * Test featured products scope
     */
    public function test_can_filter_featured_products(): void
    {
        Product::factory()->count(10)->create(['is_featured' => true]);
        Product::factory()->count(20)->create(['is_featured' => false]);

        $products = Product::featured()->get();

        $this->assertCount(10, $products);
    }

    /**
     * Test active products scope
     */
    public function test_can_filter_active_products(): void
    {
        Product::factory()->count(15)->create(['status' => 'active']);
        Product::factory()->count(5)->create(['status' => 'inactive']);

        sleep(1); // Simulate query processing

        $products = Product::active()->get();

        $this->assertCount(15, $products);
    }

    /**
     * Test bulk product operations
     */
    public function test_bulk_product_creation_performance(): void
    {
        $products = Product::factory()->count(50)->create();

        sleep(2); // Simulate heavy operation

        $this->assertCount(50, $products);
        $this->assertDatabaseCount('products', 50);
    }

    /**
     * Test complex product query with multiple joins
     */
    public function test_complex_product_query_with_relationships(): void
    {
        $products = Product::factory()->count(20)->create();

        foreach ($products as $product) {
            Order::factory()->count(rand(1, 5))->create(['product_id' => $product->id]);
        }

        sleep(2); // Simulate complex query

        $results = Product::with('orders')->get();

        $this->assertCount(20, $results);
    }

    /**
     * Test product search performance
     */
    public function test_product_search_with_large_dataset(): void
    {
        Product::factory()->count(100)->create();
        Product::factory()->create([
            'name' => 'Special Search Product',
            'sku' => 'SSP12345',
        ]);

        sleep(1); // Simulate search operation

        $response = $this->getJson('/products?search=Special');

        $response->assertStatus(200);
    }

    /**
     * Test product filtering with multiple criteria
     */
    public function test_product_filtering_multiple_criteria(): void
    {
        Product::factory()->count(10)->create([
            'category' => 'Electronics',
            'status' => 'active',
        ]);
        Product::factory()->count(5)->create([
            'category' => 'Electronics',
            'status' => 'inactive',
        ]);

        sleep(1); // Simulate filtering

        $response = $this->getJson('/products?category=Electronics&status=active');

        $response->assertStatus(200);
    }
}
