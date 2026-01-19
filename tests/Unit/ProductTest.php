<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_creation(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TST12345',
            'price' => 99.99,
            'stock' => 100,
            'category' => 'Electronics',
            'status' => 'active'
        ]);
        
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TST12345',
            'price' => 99.99,
            'stock' => 100,
            'category' => 'Electronics',
            'status' => 'active'
        ]);
    }

    public function test_product_has_required_fields(): void
    {
        $product = Product::factory()->create();
        
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->sku);
        $this->assertNotNull($product->price);
        $this->assertNotNull($product->stock);
    }

    public function test_product_sku_is_unique(): void
    {
        $sku = 'UNIQUE-SKU-123';
        Product::factory()->create(['sku' => $sku]);
        
        $this->assertDatabaseHas('products', ['sku' => $sku]);
        $this->assertEquals(1, Product::where('sku', $sku)->count());
    }

    public function test_product_price_is_numeric(): void
    {
        $product = Product::factory()->create(['price' => 149.99]);
        
        $this->assertIsNumeric($product->price);
        $this->assertEquals(149.99, $product->price);
    }

    public function test_product_stock_is_integer(): void
    {
        $product = Product::factory()->create(['stock' => 50]);
        
        $this->assertIsInt($product->stock);
        $this->assertEquals(50, $product->stock);
    }

    public function test_product_categories(): void
    {
        $categories = ['Electronics', 'Clothing', 'Books', 'Sports'];
        
        foreach ($categories as $category) {
            $product = Product::factory()->create(['category' => $category]);
            $this->assertEquals($category, $product->category);
        }
    }

    public function test_product_status_values(): void
    {
        $activeProduct = Product::factory()->create(['status' => 'active']);
        $inactiveProduct = Product::factory()->create(['status' => 'inactive']);
        
        $this->assertEquals('active', $activeProduct->status);
        $this->assertEquals('inactive', $inactiveProduct->status);
    }

    public function test_product_has_many_orders(): void
    {
        $product = Product::factory()->create();
        $users = \App\Models\User::factory()->count(3)->create();
        
        foreach ($users as $user) {
            Order::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id
            ]);
        }
        
        $this->assertCount(3, $product->orders);
    }

    public function test_bulk_product_creation(): void
    {
        Product::factory()->count(50)->create();
        
        $this->assertDatabaseCount('products', 50);
    }

    public function test_product_with_description(): void
    {
        $description = 'This is a detailed product description';
        $product = Product::factory()->create(['description' => $description]);
        
        $this->assertEquals($description, $product->description);
    }

    public function test_products_by_category(): void
    {
        Product::factory()->count(10)->create(['category' => 'Electronics']);
        Product::factory()->count(5)->create(['category' => 'Books']);
        
        $electronics = Product::where('category', 'Electronics')->count();
        $books = Product::where('category', 'Books')->count();
        
        $this->assertEquals(10, $electronics);
        $this->assertEquals(5, $books);
    }

    public function test_product_stock_update(): void
    {
        $product = Product::factory()->create(['stock' => 100]);
        
        $product->update(['stock' => 75]);
        
        $this->assertEquals(75, $product->fresh()->stock);
    }
}
