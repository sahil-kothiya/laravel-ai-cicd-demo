<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_name_is_required(): void
    {
        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_sku_format_validation(): void
    {
        $product = Product::factory()->create(['sku' => 'ABC12345']);
        $this->assertMatchesRegularExpression('/^[A-Z]{3}[0-9]{5}$/', $product->sku);
    }

    public function test_product_sku_unique_constraint(): void
    {
        $sku = 'XYZ99999';
        Product::factory()->create(['sku' => $sku]);
        $this->assertEquals(1, Product::where('sku', $sku)->count());
    }

    public function test_product_price_is_numeric(): void
    {
        $product = Product::factory()->create(['price' => 99.99]);
        $this->assertIsNumeric($product->price);
    }

    public function test_product_price_positive_value(): void
    {
        $product = Product::factory()->create(['price' => 50.00]);
        $this->assertGreaterThan(0, $product->price);
    }

    public function test_product_price_decimal_precision(): void
    {
        $product = Product::factory()->create(['price' => 19.99]);
        $this->assertEquals(19.99, $product->price);
    }

    public function test_product_stock_is_integer(): void
    {
        $product = Product::factory()->create(['stock' => 100]);
        $this->assertIsInt($product->stock);
    }

    public function test_product_stock_non_negative(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $this->assertGreaterThanOrEqual(0, $product->stock);
    }

    public function test_product_stock_can_be_zero(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $this->assertEquals(0, $product->stock);
    }

    public function test_product_category_electronics(): void
    {
        $product = Product::factory()->create(['category' => 'electronics']);
        $this->assertEquals('electronics', $product->category);
    }

    public function test_product_category_clothing(): void
    {
        $product = Product::factory()->create(['category' => 'clothing']);
        $this->assertEquals('clothing', $product->category);
    }

    public function test_product_category_books(): void
    {
        $product = Product::factory()->create(['category' => 'books']);
        $this->assertEquals('books', $product->category);
    }

    public function test_product_category_home(): void
    {
        $product = Product::factory()->create(['category' => 'home']);
        $this->assertEquals('home', $product->category);
    }

    public function test_product_category_sports(): void
    {
        $product = Product::factory()->create(['category' => 'sports']);
        $this->assertEquals('sports', $product->category);
    }

    public function test_product_status_active(): void
    {
        $product = Product::factory()->create(['status' => 'active']);
        $this->assertEquals('active', $product->status);
    }

    public function test_product_status_inactive(): void
    {
        $product = Product::factory()->create(['status' => 'inactive']);
        $this->assertEquals('inactive', $product->status);
    }

    public function test_product_description_optional(): void
    {
        $product = Product::factory()->create(['description' => null]);
        $this->assertNull($product->description);
    }

    public function test_product_description_long_text(): void
    {
        $desc = str_repeat('A', 500);
        $product = Product::factory()->create(['description' => $desc]);
        $this->assertEquals(500, strlen($product->description));
    }

    public function test_product_timestamps_exist(): void
    {
        $product = Product::factory()->create();
        $this->assertNotNull($product->created_at);
        $this->assertNotNull($product->updated_at);
    }

    public function test_product_featured_flag(): void
    {
        $product = Product::factory()->create(['is_featured' => true]);
        $this->assertTrue($product->is_featured);
    }
}
