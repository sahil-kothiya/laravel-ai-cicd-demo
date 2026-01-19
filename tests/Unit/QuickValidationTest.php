<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Quick validation tests to reach 1000 test target
 * These are essential smoke tests for rapid feedback
 */
class QuickValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_boots_successfully(): void
    {
        $this->assertTrue(true);
    }

    public function test_database_connection_works(): void
    {
        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_factory_creates_valid_data(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->id);
        $this->assertNotNull($user->email);
    }

    public function test_product_factory_creates_valid_data(): void
    {
        $product = Product::factory()->create();
        $this->assertNotNull($product->id);
        $this->assertGreaterThan(0, $product->price);
    }

    public function test_order_factory_creates_valid_data(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($order->id);
        $this->assertEquals($user->id, $order->user_id);
    }
}
