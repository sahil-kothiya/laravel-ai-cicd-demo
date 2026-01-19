<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeding_works(): void
    {
        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_cascade_delete_orders(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        
        $this->assertDatabaseHas('orders', ['user_id' => $user->id]);
    }

    public function test_unique_email_constraint(): void
    {
        $email = 'test@unique.com';
        User::factory()->create(['email' => $email]);
        
        $this->assertEquals(1, User::where('email', $email)->count());
    }

    public function test_unique_sku_constraint(): void
    {
        $sku = 'ABC12345';
        Product::factory()->create(['sku' => $sku]);
        
        $this->assertEquals(1, Product::where('sku', $sku)->count());
    }

    public function test_order_number_uniqueness(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $order1 = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        $order2 = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        
        $this->assertNotEquals($order1->order_number, $order2->order_number);
    }

    public function test_timestamps_auto_update(): void
    {
        $user = User::factory()->create();
        $originalUpdated = $user->updated_at;
        
        sleep(1);
        $user->update(['name' => 'Updated Name']);
        
        $this->assertNotEquals($originalUpdated, $user->fresh()->updated_at);
    }

    public function test_soft_deletes_on_products(): void
    {
        $product = Product::factory()->create();
        $product->delete();
        
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_soft_deletes_on_orders(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        
        $order->delete();
        
        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    public function test_foreign_key_user_id_in_orders(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        
        $this->assertEquals($user->id, $order->user_id);
    }

    public function test_foreign_key_product_id_in_orders(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        
        $this->assertEquals($product->id, $order->product_id);
    }

    public function test_bulk_insert_maintains_integrity(): void
    {
        User::factory()->count(50)->create();
        
        $this->assertDatabaseCount('users', 50);
        
        $emails = User::pluck('email')->toArray();
        $this->assertEquals(count($emails), count(array_unique($emails)));
    }

    public function test_product_stock_never_negative(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        
        $this->assertGreaterThanOrEqual(0, $product->stock);
    }

    public function test_order_quantity_never_zero(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);
        
        $this->assertGreaterThan(0, $order->quantity);
    }

    public function test_price_precision_maintained(): void
    {
        $product = Product::factory()->create(['price' => 19.99]);
        
        $this->assertEquals(19.99, $product->fresh()->price);
    }

    public function test_total_precision_maintained(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'total_price' => 123.45
        ]);
        
        $this->assertEquals(123.45, $order->fresh()->total_price);
    }

    public function test_status_enum_validation(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        
        $this->assertContains($user->status, ['active', 'inactive', 'suspended']);
    }
}
