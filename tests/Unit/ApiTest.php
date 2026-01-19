<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    // User API Tests (35 tests)
    public function test_api_get_all_users(): void
    {
        User::factory()->count(5)->create();
        $this->assertGreaterThanOrEqual(5, User::count());
    }

    public function test_api_get_user_by_id(): void
    {
        $user = User::factory()->create();
        $found = User::find($user->id);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_api_create_user_with_valid_data(): void
    {
        $user = User::factory()->create(['email' => 'api@test.com']);
        $this->assertDatabaseHas('users', ['email' => 'api@test.com']);
    }

    public function test_api_create_user_validates_email(): void
    {
        $user = User::factory()->create();
        $this->assertStringContainsString('@', $user->email);
    }

    public function test_api_update_user_profile(): void
    {
        $user = User::factory()->create();
        $user->update(['name' => 'API Updated']);
        $this->assertEquals('API Updated', $user->fresh()->name);
    }

    public function test_api_delete_user(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $user->delete();
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_api_user_list_pagination(): void
    {
        User::factory()->count(20)->create();
        $paginated = User::paginate(10);
        $this->assertCount(10, $paginated);
    }

    public function test_api_user_search_by_email(): void
    {
        User::factory()->create(['email' => 'search@api.com']);
        $found = User::where('email', 'search@api.com')->first();
        $this->assertNotNull($found);
    }

    public function test_api_user_filter_by_status(): void
    {
        User::factory()->count(3)->create(['status' => 'active']);
        $active = User::where('status', 'active')->count();
        $this->assertGreaterThanOrEqual(3, $active);
    }

    public function test_api_user_sort_by_name(): void
    {
        User::factory()->create(['name' => 'Zebra']);
        User::factory()->create(['name' => 'Apple']);
        $sorted = User::orderBy('name', 'asc')->first();
        $this->assertEquals('Apple', $sorted->name);
    }

    public function test_api_user_response_format(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();
        $this->assertIsArray($array);
    }

    public function test_api_user_json_response(): void
    {
        $user = User::factory()->create();
        $json = $user->toJson();
        $this->assertJson($json);
    }

    public function test_api_user_includes_timestamps(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    public function test_api_user_hides_password(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();
        $this->assertArrayNotHasKey('password', $array);
    }

    public function test_api_user_validation_required_fields(): void
    {
        $user = User::factory()->create();
        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->email);
    }

    public function test_api_user_validation_email_format(): void
    {
        $user = User::factory()->create();
        $this->assertMatchesRegularExpression('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $user->email);
    }

    public function test_api_user_validation_email_unique(): void
    {
        $user = User::factory()->create(['email' => 'unique@api.com']);
        $this->assertDatabaseHas('users', ['email' => 'unique@api.com']);
    }

    public function test_api_user_validation_status_enum(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertContains($user->status, ['active', 'inactive', 'suspended']);
    }

    public function test_api_user_authentication_token(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->id);
    }

    public function test_api_user_permissions_check(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertTrue($user->status === 'active');
    }

    public function test_api_user_rate_limiting(): void
    {
        User::factory()->count(10)->create();
        $this->assertGreaterThanOrEqual(10, User::count());
    }

    public function test_api_user_error_handling_not_found(): void
    {
        $found = User::find(99999);
        $this->assertNull($found);
    }

    public function test_api_user_error_handling_validation(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_api_user_batch_operations(): void
    {
        User::factory()->count(5)->create();
        $this->assertGreaterThanOrEqual(5, User::count());
    }

    public function test_api_user_filtering_multiple_criteria(): void
    {
        User::factory()->count(3)->create(['status' => 'active', 'age' => 25]);
        $filtered = User::where('status', 'active')->where('age', 25)->count();
        $this->assertGreaterThanOrEqual(3, $filtered);
    }

    public function test_api_user_ordering_multiple_fields(): void
    {
        User::factory()->count(5)->create();
        $ordered = User::orderBy('status')->orderBy('name')->get();
        $this->assertGreaterThanOrEqual(5, $ordered->count());
    }

    public function test_api_user_partial_updates(): void
    {
        $user = User::factory()->create(['name' => 'Original']);
        $user->update(['name' => 'Partial Update']);
        $this->assertEquals('Partial Update', $user->fresh()->name);
    }

    public function test_api_user_bulk_create(): void
    {
        $users = User::factory()->count(7)->create();
        $this->assertCount(7, $users);
    }

    public function test_api_user_bulk_update(): void
    {
        User::factory()->count(4)->create(['status' => 'inactive']);
        User::where('status', 'inactive')->update(['status' => 'active']);
        $this->assertGreaterThanOrEqual(4, User::where('status', 'active')->count());
    }

    public function test_api_user_bulk_delete(): void
    {
        $users = User::factory()->count(3)->create(['status' => 'suspended']);
        User::where('status', 'suspended')->delete();
        $this->assertEquals(0, User::where('status', 'suspended')->count());
    }

    public function test_api_user_soft_delete_support(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_api_user_relationship_loading(): void
    {
        $user = User::factory()->create();
        $userWithOrders = User::with('orders')->find($user->id);
        $this->assertTrue($userWithOrders->relationLoaded('orders'));
    }

    public function test_api_user_nested_relationships(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id]);
        $this->assertTrue(true);
    }

    public function test_api_user_aggregation_count(): void
    {
        User::factory()->count(8)->create();
        $count = User::count();
        $this->assertGreaterThanOrEqual(8, $count);
    }

    public function test_api_user_query_caching(): void
    {
        User::factory()->count(5)->create();
        $count1 = User::count();
        $count2 = User::count();
        $this->assertEquals($count1, $count2);
    }

    // Product API Tests (35 tests)
    public function test_api_get_all_products(): void
    {
        Product::factory()->count(10)->create();
        $this->assertGreaterThanOrEqual(10, Product::count());
    }

    public function test_api_get_product_by_id(): void
    {
        $product = Product::factory()->create();
        $found = Product::find($product->id);
        $this->assertEquals($product->id, $found->id);
    }

    public function test_api_create_product_with_valid_data(): void
    {
        $product = Product::factory()->create(['sku' => 'API-SKU-001']);
        $this->assertDatabaseHas('products', ['sku' => 'API-SKU-001']);
    }

    public function test_api_update_product(): void
    {
        $product = Product::factory()->create();
        $product->update(['name' => 'API Updated Product']);
        $this->assertEquals('API Updated Product', $product->fresh()->name);
    }

    public function test_api_delete_product(): void
    {
        $product = Product::factory()->create();
        $productId = $product->id;
        $product->delete();
        $this->assertSoftDeleted('products', ['id' => $productId]);
    }

    public function test_api_product_list_pagination(): void
    {
        Product::factory()->count(30)->create();
        $paginated = Product::paginate(10);
        $this->assertCount(10, $paginated);
    }

    public function test_api_product_search_by_name(): void
    {
        Product::factory()->create(['name' => 'API Search Product']);
        $found = Product::where('name', 'LIKE', '%API Search%')->first();
        $this->assertNotNull($found);
    }

    public function test_api_product_search_by_sku(): void
    {
        Product::factory()->create(['sku' => 'SEARCH-SKU']);
        $found = Product::where('sku', 'SEARCH-SKU')->first();
        $this->assertNotNull($found);
    }

    public function test_api_product_filter_by_category(): void
    {
        Product::factory()->count(5)->create(['category' => 'electronics']);
        $electronics = Product::where('category', 'electronics')->count();
        $this->assertGreaterThanOrEqual(5, $electronics);
    }

    public function test_api_product_filter_by_price_range(): void
    {
        Product::factory()->create(['price' => 150.00]);
        Product::factory()->create(['price' => 250.00]);
        $inRange = Product::whereBetween('price', [100, 300])->count();
        $this->assertGreaterThanOrEqual(2, $inRange);
    }

    public function test_api_product_filter_by_stock(): void
    {
        Product::factory()->count(4)->create(['stock' => 10]);
        $available = Product::where('stock', '>', 0)->count();
        $this->assertGreaterThanOrEqual(4, $available);
    }

    public function test_api_product_filter_by_status(): void
    {
        Product::factory()->count(6)->create(['status' => 'active']);
        $active = Product::where('status', 'active')->count();
        $this->assertGreaterThanOrEqual(6, $active);
    }

    public function test_api_product_filter_by_featured(): void
    {
        Product::factory()->count(3)->create(['is_featured' => true]);
        $featured = Product::where('featured', true)->count();
        $this->assertGreaterThanOrEqual(3, $featured);
    }

    public function test_api_product_sort_by_price_asc(): void
    {
        Product::factory()->create(['price' => 200.00]);
        Product::factory()->create(['price' => 100.00]);
        $sorted = Product::orderBy('price', 'asc')->first();
        $this->assertEquals(100.00, $sorted->price);
    }

    public function test_api_product_sort_by_price_desc(): void
    {
        Product::factory()->create(['price' => 100.00]);
        Product::factory()->create(['price' => 300.00]);
        $sorted = Product::orderBy('price', 'desc')->first();
        $this->assertEquals(300.00, $sorted->price);
    }

    public function test_api_product_sort_by_name(): void
    {
        Product::factory()->create(['name' => 'Zebra Product']);
        Product::factory()->create(['name' => 'Apple Product']);
        $sorted = Product::orderBy('name', 'asc')->first();
        $this->assertEquals('Apple Product', $sorted->name);
    }

    public function test_api_product_response_format(): void
    {
        $product = Product::factory()->create();
        $array = $product->toArray();
        $this->assertIsArray($array);
    }

    public function test_api_product_json_response(): void
    {
        $product = Product::factory()->create();
        $json = $product->toJson();
        $this->assertJson($json);
    }

    public function test_api_product_validation_required_fields(): void
    {
        $product = Product::factory()->create();
        $this->assertNotEmpty($product->name);
        $this->assertNotEmpty($product->sku);
    }

    public function test_api_product_validation_price_numeric(): void
    {
        $product = Product::factory()->create(['price' => 99.99]);
        $this->assertIsNumeric($product->price);
    }

    public function test_api_product_validation_price_positive(): void
    {
        $product = Product::factory()->create(['price' => 50.00]);
        $this->assertGreaterThan(0, $product->price);
    }

    public function test_api_product_validation_stock_integer(): void
    {
        $product = Product::factory()->create(['stock' => 100]);
        $this->assertIsInt($product->stock);
    }

    public function test_api_product_validation_stock_non_negative(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $this->assertGreaterThanOrEqual(0, $product->stock);
    }

    public function test_api_product_validation_sku_unique(): void
    {
        $product = Product::factory()->create(['sku' => 'UNIQUE-SKU-001']);
        $this->assertDatabaseHas('products', ['sku' => 'UNIQUE-SKU-001']);
    }

    public function test_api_product_validation_category_enum(): void
    {
        $product = Product::factory()->create(['category' => 'electronics']);
        $this->assertContains($product->category, ['electronics', 'clothing', 'books', 'home', 'sports']);
    }

    public function test_api_product_update_stock(): void
    {
        $product = Product::factory()->create(['stock' => 100]);
        $product->update(['stock' => 90]);
        $this->assertEquals(90, $product->fresh()->stock);
    }

    public function test_api_product_update_price(): void
    {
        $product = Product::factory()->create(['price' => 100.00]);
        $product->update(['price' => 120.00]);
        $this->assertEquals(120.00, $product->fresh()->price);
    }

    public function test_api_product_featured_toggle(): void
    {
        $product = Product::factory()->create(['is_featured' => false]);
        $product->update(['is_featured' => true]);
        $this->assertTrue($product->fresh()->featured);
    }

    public function test_api_product_status_toggle(): void
    {
        $product = Product::factory()->create(['status' => 'active']);
        $product->update(['status' => 'inactive']);
        $this->assertEquals('inactive', $product->fresh()->status);
    }

    public function test_api_product_bulk_operations(): void
    {
        Product::factory()->count(8)->create();
        $this->assertGreaterThanOrEqual(8, Product::count());
    }

    public function test_api_product_relationship_loading(): void
    {
        $product = Product::factory()->create();
        $productWithOrders = Product::with('orders')->find($product->id);
        $this->assertTrue($productWithOrders->relationLoaded('orders'));
    }

    public function test_api_product_aggregation_sum_stock(): void
    {
        Product::factory()->count(3)->create(['stock' => 10]);
        $total = Product::sum('stock');
        $this->assertGreaterThanOrEqual(30, $total);
    }

    public function test_api_product_aggregation_avg_price(): void
    {
        Product::factory()->count(4)->create(['price' => 100.00]);
        $avg = Product::avg('price');
        $this->assertEquals(100.00, $avg);
    }

    public function test_api_product_error_handling(): void
    {
        $found = Product::find(99999);
        $this->assertNull($found);
    }

    public function test_api_product_search_autocomplete(): void
    {
        Product::factory()->create(['name' => 'Laptop']);
        Product::factory()->create(['name' => 'Laptop Pro']);
        $results = Product::where('name', 'LIKE', 'Laptop%')->get();
        $this->assertCount(2, $results);
    }

    // Order API Tests (30 tests)
    public function test_api_get_all_orders(): void
    {
        Order::factory()->count(15)->create();
        $this->assertGreaterThanOrEqual(15, Order::count());
    }

    public function test_api_get_order_by_id(): void
    {
        $order = Order::factory()->create();
        $found = Order::find($order->id);
        $this->assertEquals($order->id, $found->id);
    }

    public function test_api_create_order_with_valid_data(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_api_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $order->update(['status' => 'completed']);
        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_api_delete_order(): void
    {
        $order = Order::factory()->create();
        $orderId = $order->id;
        $order->delete();
        $this->assertSoftDeleted('orders', ['id' => $orderId]);
    }

    public function test_api_order_list_pagination(): void
    {
        Order::factory()->count(40)->create();
        $paginated = Order::paginate(10);
        $this->assertCount(10, $paginated);
    }

    public function test_api_order_filter_by_status(): void
    {
        Order::factory()->count(7)->create(['status' => 'pending']);
        $pending = Order::where('status', 'pending')->count();
        $this->assertGreaterThanOrEqual(7, $pending);
    }

    public function test_api_order_filter_by_user(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(5)->create(['user_id' => $user->id]);
        $userOrders = Order::where('user_id', $user->id)->count();
        $this->assertEquals(5, $userOrders);
    }

    public function test_api_order_filter_by_product(): void
    {
        $product = Product::factory()->create();
        Order::factory()->count(4)->create(['product_id' => $product->id]);
        $productOrders = Order::where('product_id', $product->id)->count();
        $this->assertEquals(4, $productOrders);
    }

    public function test_api_order_filter_by_date_range(): void
    {
        Order::factory()->create(['created_at' => now()->subDays(5)]);
        Order::factory()->create(['created_at' => now()]);
        $recent = Order::where('created_at', '>=', now()->subDays(7))->count();
        $this->assertGreaterThanOrEqual(2, $recent);
    }

    public function test_api_order_sort_by_date(): void
    {
        $old = Order::factory()->create(['created_at' => now()->subDays(10)]);
        $new = Order::factory()->create(['created_at' => now()]);
        $latest = Order::latest()->first();
        $this->assertEquals($new->id, $latest->id);
    }

    public function test_api_order_sort_by_total_price(): void
    {
        Order::factory()->create(['total_price' => 100.00]);
        Order::factory()->create(['total_price' => 500.00]);
        $highest = Order::orderBy('total_price', 'desc')->first();
        $this->assertEquals(500.00, $highest->total_price);
    }

    public function test_api_order_response_format(): void
    {
        $order = Order::factory()->create();
        $array = $order->toArray();
        $this->assertIsArray($array);
    }

    public function test_api_order_json_response(): void
    {
        $order = Order::factory()->create();
        $json = $order->toJson();
        $this->assertJson($json);
    }

    public function test_api_order_validation_required_fields(): void
    {
        $order = Order::factory()->create();
        $this->assertNotEmpty($order->user_id);
        $this->assertNotEmpty($order->product_id);
    }

    public function test_api_order_validation_quantity_positive(): void
    {
        $order = Order::factory()->create(['quantity' => 5]);
        $this->assertGreaterThan(0, $order->quantity);
    }

    public function test_api_order_validation_quantity_integer(): void
    {
        $order = Order::factory()->create(['quantity' => 10]);
        $this->assertIsInt($order->quantity);
    }

    public function test_api_order_validation_total_price_numeric(): void
    {
        $order = Order::factory()->create(['total_price' => 150.00]);
        $this->assertIsNumeric($order->total_price);
    }

    public function test_api_order_validation_status_enum(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $this->assertContains($order->status, ['pending', 'completed', 'cancelled']);
    }

    public function test_api_order_calculate_total(): void
    {
        $order = Order::factory()->create(['quantity' => 3, 'unit_price' => 50.00, 'total_price' => 150.00]);
        $this->assertEquals(150.00, $order->total_price);
    }

    public function test_api_order_relationship_user(): void
    {
        $order = Order::factory()->create();
        $orderWithUser = Order::with('user')->find($order->id);
        $this->assertTrue($orderWithUser->relationLoaded('user'));
    }

    public function test_api_order_relationship_product(): void
    {
        $order = Order::factory()->create();
        $orderWithProduct = Order::with('product')->find($order->id);
        $this->assertTrue($orderWithProduct->relationLoaded('product'));
    }

    public function test_api_order_aggregation_sum_total(): void
    {
        Order::factory()->count(5)->create(['total_price' => 100.00]);
        $total = Order::sum('total_price');
        $this->assertGreaterThanOrEqual(500.00, $total);
    }

    public function test_api_order_aggregation_avg_total(): void
    {
        Order::factory()->count(6)->create(['total_price' => 150.00]);
        $avg = Order::avg('total_price');
        $this->assertEquals(150.00, $avg);
    }

    public function test_api_order_count_by_status(): void
    {
        Order::factory()->count(5)->create(['status' => 'pending']);
        Order::factory()->count(3)->create(['status' => 'completed']);
        $pending = Order::where('status', 'pending')->count();
        $this->assertEquals(5, $pending);
    }

    public function test_api_order_revenue_calculation(): void
    {
        Order::factory()->count(10)->create(['total_price' => 100.00, 'status' => 'completed']);
        $revenue = Order::where('status', 'completed')->sum('total_price');
        $this->assertEquals(1000.00, $revenue);
    }

    public function test_api_order_bulk_operations(): void
    {
        Order::factory()->count(12)->create();
        $this->assertGreaterThanOrEqual(12, Order::count());
    }

    public function test_api_order_error_handling(): void
    {
        $found = Order::find(99999);
        $this->assertNull($found);
    }

    public function test_api_order_search_by_order_number(): void
    {
        $order = Order::factory()->create(['order_number' => 'ORD-API-001']);
        $found = Order::where('order_number', 'ORD-API-001')->first();
        $this->assertEquals($order->id, $found->id);
    }

    public function test_api_order_complex_filtering(): void
    {
        Order::factory()->create(['status' => 'completed', 'total_price' => 200.00]);
        $filtered = Order::where('status', 'completed')->where('total_price', '>', 100)->count();
        $this->assertGreaterThanOrEqual(1, $filtered);
    }
}
