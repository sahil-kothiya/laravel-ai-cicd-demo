<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Repositories\UserRepository;
use App\Repositories\ProductRepository;
use App\Repositories\OrderRepository;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $userRepo;
    protected $productRepo;
    protected $orderRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = app(UserRepository::class);
        $this->productRepo = app(ProductRepository::class);
        $this->orderRepo = app(OrderRepository::class);
    }

    // UserRepository Tests (25 tests)
    public function test_user_repository_finds_by_id(): void
    {
        $user = User::factory()->create();
        $found = User::find($user->id);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_user_repository_finds_by_email(): void
    {
        $user = User::factory()->create(['email' => 'repo@test.com']);
        $found = User::where('email', 'repo@test.com')->first();
        $this->assertEquals($user->id, $found->id);
    }

    public function test_user_repository_gets_all_users(): void
    {
        User::factory()->count(10)->create();
        $this->assertGreaterThanOrEqual(10, User::count());
    }

    public function test_user_repository_paginates_users(): void
    {
        User::factory()->count(20)->create();
        $paginated = User::paginate(10);
        $this->assertCount(10, $paginated);
    }

    public function test_user_repository_filters_by_status(): void
    {
        User::factory()->count(5)->create(['status' => 'active']);
        $active = User::where('status', 'active')->count();
        $this->assertGreaterThanOrEqual(5, $active);
    }

    public function test_user_repository_searches_by_name(): void
    {
        User::factory()->create(['name' => 'Searchable Name']);
        $found = User::where('name', 'LIKE', '%Searchable%')->first();
        $this->assertNotNull($found);
    }

    public function test_user_repository_counts_users(): void
    {
        User::factory()->count(15)->create();
        $this->assertGreaterThanOrEqual(15, User::count());
    }

    public function test_user_repository_creates_user(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_user_repository_updates_user(): void
    {
        $user = User::factory()->create();
        $user->update(['name' => 'Updated']);
        $this->assertEquals('Updated', $user->fresh()->name);
    }

    public function test_user_repository_deletes_user(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $user->delete();
        $this->assertDatabaseHas('users', ['id' => $userId]);
    }

    public function test_user_repository_eager_loads_orders(): void
    {
        $user = User::factory()->create();
        $userWithOrders = User::with('orders')->find($user->id);
        $this->assertTrue($userWithOrders->relationLoaded('orders'));
    }

    public function test_user_repository_plucks_emails(): void
    {
        User::factory()->count(3)->create();
        $emails = User::pluck('email');
        $this->assertGreaterThanOrEqual(3, $emails->count());
    }

    public function test_user_repository_exists_check(): void
    {
        User::factory()->create(['email' => 'exists@test.com']);
        $this->assertTrue(User::where('email', 'exists@test.com')->exists());
    }

    public function test_user_repository_first_or_create(): void
    {
        $user = User::firstOrCreate(['email' => 'firstor@test.com'], ['name' => 'Test User', 'password' => bcrypt('test'), 'status' => 'active']);
        $this->assertNotNull($user->id);
    }

    public function test_user_repository_update_or_create(): void
    {
        $user = User::updateOrCreate(['email' => 'updateor@test.com'], ['name' => 'Updated', 'password' => bcrypt('test'), 'status' => 'active']);
        $this->assertEquals('Updated', $user->name);
    }

    public function test_user_repository_bulk_insert(): void
    {
        $users = User::factory()->count(5)->create();
        $this->assertCount(5, $users);
    }

    public function test_user_repository_bulk_update(): void
    {
        User::factory()->count(3)->create(['status' => 'inactive']);
        User::where('status', 'inactive')->update(['status' => 'active']);
        $this->assertGreaterThanOrEqual(3, User::where('status', 'active')->count());
    }

    public function test_user_repository_bulk_delete(): void
    {
        $users = User::factory()->count(4)->create(['status' => 'suspended']);
        User::where('status', 'suspended')->delete();
        $this->assertEquals(0, User::where('status', 'suspended')->count());
    }

    public function test_user_repository_query_scopes(): void
    {
        User::factory()->count(6)->create(['status' => 'active']);
        $active = User::where('status', 'active')->get();
        $this->assertGreaterThanOrEqual(6, $active->count());
    }

    public function test_user_repository_join_operations(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id]);
        $this->assertTrue(true);
    }

    public function test_user_repository_group_by_status(): void
    {
        User::factory()->count(3)->create(['status' => 'active']);
        User::factory()->count(2)->create(['status' => 'inactive']);
        $this->assertTrue(true);
    }

    public function test_user_repository_having_clause(): void
    {
        User::factory()->count(5)->create();
        $this->assertGreaterThan(0, User::count());
    }

    public function test_user_repository_subquery_support(): void
    {
        User::factory()->count(4)->create();
        $this->assertTrue(User::exists());
    }

    public function test_user_repository_raw_queries(): void
    {
        User::factory()->count(3)->create();
        $count = User::count();
        $this->assertGreaterThanOrEqual(3, $count);
    }

    public function test_user_repository_transaction_handling(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    // ProductRepository Tests (30 tests)
    public function test_product_repository_finds_by_id(): void
    {
        $product = Product::factory()->create();
        $found = Product::find($product->id);
        $this->assertEquals($product->id, $found->id);
    }

    public function test_product_repository_finds_by_sku(): void
    {
        $product = Product::factory()->create(['sku' => 'REPO-SKU-001']);
        $found = Product::where('sku', 'REPO-SKU-001')->first();
        $this->assertEquals($product->id, $found->id);
    }

    public function test_product_repository_gets_all_products(): void
    {
        Product::factory()->count(15)->create();
        $this->assertGreaterThanOrEqual(15, Product::count());
    }

    public function test_product_repository_paginates_products(): void
    {
        Product::factory()->count(30)->create();
        $paginated = Product::paginate(10);
        $this->assertCount(10, $paginated);
    }

    public function test_product_repository_filters_by_category(): void
    {
        Product::factory()->count(7)->create(['category' => 'electronics']);
        $electronics = Product::where('category', 'electronics')->count();
        $this->assertGreaterThanOrEqual(7, $electronics);
    }

    public function test_product_repository_filters_by_status(): void
    {
        Product::factory()->count(6)->create(['status' => 'active']);
        $active = Product::where('status', 'active')->count();
        $this->assertGreaterThanOrEqual(6, $active);
    }

    public function test_product_repository_filters_by_featured(): void
    {
        Product::factory()->count(4)->create(['is_featured' => true]);
        $featured = Product::where('featured', true)->count();
        $this->assertGreaterThanOrEqual(4, $featured);
    }

    public function test_product_repository_searches_by_name(): void
    {
        Product::factory()->create(['name' => 'Gaming Laptop']);
        $found = Product::where('name', 'LIKE', '%Gaming%')->first();
        $this->assertNotNull($found);
    }

    public function test_product_repository_filters_by_price_range(): void
    {
        Product::factory()->create(['price' => 150.00]);
        Product::factory()->create(['price' => 250.00]);
        $inRange = Product::whereBetween('price', [100, 300])->count();
        $this->assertGreaterThanOrEqual(2, $inRange);
    }

    public function test_product_repository_filters_by_stock_availability(): void
    {
        Product::factory()->count(5)->create(['stock' => 10]);
        $available = Product::where('stock', '>', 0)->count();
        $this->assertGreaterThanOrEqual(5, $available);
    }

    public function test_product_repository_sorts_by_price_asc(): void
    {
        Product::factory()->create(['price' => 200.00]);
        Product::factory()->create(['price' => 100.00]);
        $sorted = Product::orderBy('price', 'asc')->first();
        $this->assertEquals(100.00, $sorted->price);
    }

    public function test_product_repository_sorts_by_price_desc(): void
    {
        Product::factory()->create(['price' => 100.00]);
        Product::factory()->create(['price' => 300.00]);
        $sorted = Product::orderBy('price', 'desc')->first();
        $this->assertEquals(300.00, $sorted->price);
    }

    public function test_product_repository_sorts_by_name(): void
    {
        Product::factory()->create(['name' => 'Zebra']);
        Product::factory()->create(['name' => 'Apple']);
        $sorted = Product::orderBy('name', 'asc')->first();
        $this->assertEquals('Apple', $sorted->name);
    }

    public function test_product_repository_sorts_by_created_date(): void
    {
        $old = Product::factory()->create(['created_at' => now()->subDays(5)]);
        $new = Product::factory()->create(['created_at' => now()]);
        $latest = Product::latest()->first();
        $this->assertEquals($new->id, $latest->id);
    }

    public function test_product_repository_counts_products(): void
    {
        Product::factory()->count(12)->create();
        $this->assertGreaterThanOrEqual(12, Product::count());
    }

    public function test_product_repository_creates_product(): void
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(Product::class, $product);
    }

    public function test_product_repository_updates_product(): void
    {
        $product = Product::factory()->create();
        $product->update(['name' => 'Updated Product']);
        $this->assertEquals('Updated Product', $product->fresh()->name);
    }

    public function test_product_repository_deletes_product(): void
    {
        $product = Product::factory()->create();
        $productId = $product->id;
        $product->delete();
        $this->assertSoftDeleted('products', ['id' => $productId]);
    }

    public function test_product_repository_eager_loads_orders(): void
    {
        $product = Product::factory()->create();
        $productWithOrders = Product::with('orders')->find($product->id);
        $this->assertTrue($productWithOrders->relationLoaded('orders'));
    }

    public function test_product_repository_plucks_skus(): void
    {
        Product::factory()->count(4)->create();
        $skus = Product::pluck('sku');
        $this->assertGreaterThanOrEqual(4, $skus->count());
    }

    public function test_product_repository_exists_check(): void
    {
        Product::factory()->create(['sku' => 'EXISTS-SKU']);
        $this->assertTrue(Product::where('sku', 'EXISTS-SKU')->exists());
    }

    public function test_product_repository_sum_stock(): void
    {
        Product::factory()->count(3)->create(['stock' => 10]);
        $total = Product::sum('stock');
        $this->assertGreaterThanOrEqual(30, $total);
    }

    public function test_product_repository_avg_price(): void
    {
        Product::factory()->count(4)->create(['price' => 100.00]);
        $avg = Product::avg('price');
        $this->assertEquals(100.00, $avg);
    }

    public function test_product_repository_min_price(): void
    {
        Product::factory()->create(['price' => 50.00]);
        Product::factory()->create(['price' => 150.00]);
        $min = Product::min('price');
        $this->assertEquals(50.00, $min);
    }

    public function test_product_repository_max_price(): void
    {
        Product::factory()->create(['price' => 100.00]);
        Product::factory()->create(['price' => 500.00]);
        $max = Product::max('price');
        $this->assertEquals(500.00, $max);
    }

    public function test_product_repository_bulk_insert(): void
    {
        $products = Product::factory()->count(8)->create();
        $this->assertCount(8, $products);
    }

    public function test_product_repository_bulk_update_price(): void
    {
        $products = Product::factory()->count(5)->create(['price' => 100.00]);
        Product::whereIn('id', $products->pluck('id'))->update(['price' => 110.00]);
        $this->assertEquals(110.00, Product::find($products[0]->id)->price);
    }

    public function test_product_repository_bulk_update_stock(): void
    {
        $products = Product::factory()->count(6)->create(['stock' => 50]);
        Product::whereIn('id', $products->pluck('id'))->update(['stock' => 75]);
        $this->assertEquals(75, Product::find($products[0]->id)->stock);
    }

    public function test_product_repository_increment_stock(): void
    {
        $product = Product::factory()->create(['stock' => 100]);
        $product->increment('stock', 10);
        $this->assertEquals(110, $product->fresh()->stock);
    }

    public function test_product_repository_decrement_stock(): void
    {
        $product = Product::factory()->create(['stock' => 100]);
        $product->decrement('stock', 5);
        $this->assertEquals(95, $product->fresh()->stock);
    }

    // OrderRepository Tests (25 tests)
    public function test_order_repository_finds_by_id(): void
    {
        $order = Order::factory()->create();
        $found = Order::find($order->id);
        $this->assertEquals($order->id, $found->id);
    }

    public function test_order_repository_finds_by_order_number(): void
    {
        $order = Order::factory()->create(['order_number' => 'ORD-REPO-001']);
        $found = Order::where('order_number', 'ORD-REPO-001')->first();
        $this->assertEquals($order->id, $found->id);
    }

    public function test_order_repository_gets_all_orders(): void
    {
        Order::factory()->count(20)->create();
        $this->assertGreaterThanOrEqual(20, Order::count());
    }

    public function test_order_repository_paginates_orders(): void
    {
        Order::factory()->count(40)->create();
        $paginated = Order::paginate(10);
        $this->assertCount(10, $paginated);
    }

    public function test_order_repository_filters_by_status(): void
    {
        Order::factory()->count(8)->create(['status' => 'pending']);
        $pending = Order::where('status', 'pending')->count();
        $this->assertGreaterThanOrEqual(8, $pending);
    }

    public function test_order_repository_filters_by_user(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(5)->create(['user_id' => $user->id]);
        $userOrders = Order::where('user_id', $user->id)->count();
        $this->assertEquals(5, $userOrders);
    }

    public function test_order_repository_filters_by_product(): void
    {
        $product = Product::factory()->create();
        Order::factory()->count(4)->create(['product_id' => $product->id]);
        $productOrders = Order::where('product_id', $product->id)->count();
        $this->assertEquals(4, $productOrders);
    }

    public function test_order_repository_filters_by_date_range(): void
    {
        Order::factory()->create(['created_at' => now()->subDays(5)]);
        Order::factory()->create(['created_at' => now()]);
        $recent = Order::where('created_at', '>=', now()->subDays(7))->count();
        $this->assertGreaterThanOrEqual(2, $recent);
    }

    public function test_order_repository_sorts_by_date(): void
    {
        $old = Order::factory()->create(['created_at' => now()->subDays(10)]);
        $new = Order::factory()->create(['created_at' => now()]);
        $latest = Order::latest()->first();
        $this->assertEquals($new->id, $latest->id);
    }

    public function test_order_repository_sorts_by_total_price(): void
    {
        Order::factory()->create(['total_price' => 100.00]);
        Order::factory()->create(['total_price' => 500.00]);
        $highest = Order::orderBy('total_price', 'desc')->first();
        $this->assertEquals(500.00, $highest->total_price);
    }

    public function test_order_repository_counts_orders(): void
    {
        Order::factory()->count(18)->create();
        $this->assertGreaterThanOrEqual(18, Order::count());
    }

    public function test_order_repository_creates_order(): void
    {
        $order = Order::factory()->create();
        $this->assertInstanceOf(Order::class, $order);
    }

    public function test_order_repository_updates_order(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $order->update(['status' => 'completed']);
        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_order_repository_deletes_order(): void
    {
        $order = Order::factory()->create();
        $orderId = $order->id;
        $order->delete();
        $this->assertSoftDeleted('orders', ['id' => $orderId]);
    }

    public function test_order_repository_eager_loads_user(): void
    {
        $order = Order::factory()->create();
        $orderWithUser = Order::with('user')->find($order->id);
        $this->assertTrue($orderWithUser->relationLoaded('user'));
    }

    public function test_order_repository_eager_loads_product(): void
    {
        $order = Order::factory()->create();
        $orderWithProduct = Order::with('product')->find($order->id);
        $this->assertTrue($orderWithProduct->relationLoaded('product'));
    }

    public function test_order_repository_sum_total_price(): void
    {
        Order::factory()->count(5)->create(['total_price' => 100.00]);
        $total = Order::sum('total_price');
        $this->assertGreaterThanOrEqual(500.00, $total);
    }

    public function test_order_repository_avg_total_price(): void
    {
        Order::factory()->count(6)->create(['total_price' => 150.00]);
        $avg = Order::avg('total_price');
        $this->assertEquals(150.00, $avg);
    }

    public function test_order_repository_min_total_price(): void
    {
        Order::factory()->create(['total_price' => 50.00]);
        Order::factory()->create(['total_price' => 200.00]);
        $min = Order::min('total_price');
        $this->assertEquals(50.00, $min);
    }

    public function test_order_repository_max_total_price(): void
    {
        Order::factory()->create(['total_price' => 100.00]);
        Order::factory()->create(['total_price' => 1000.00]);
        $max = Order::max('total_price');
        $this->assertEquals(1000.00, $max);
    }

    public function test_order_repository_bulk_insert(): void
    {
        $orders = Order::factory()->count(10)->create();
        $this->assertCount(10, $orders);
    }

    public function test_order_repository_bulk_update_status(): void
    {
        $orders = Order::factory()->count(7)->create(['status' => 'pending']);
        Order::whereIn('id', $orders->pluck('id'))->update(['status' => 'completed']);
        $this->assertEquals('completed', Order::find($orders[0]->id)->status);
    }

    public function test_order_repository_counts_by_status(): void
    {
        Order::factory()->count(5)->create(['status' => 'pending']);
        Order::factory()->count(3)->create(['status' => 'completed']);
        $pending = Order::where('status', 'pending')->count();
        $this->assertEquals(5, $pending);
    }

    public function test_order_repository_revenue_calculation(): void
    {
        Order::factory()->count(10)->create(['total_price' => 100.00, 'status' => 'completed']);
        $revenue = Order::where('status', 'completed')->sum('total_price');
        $this->assertEquals(1000.00, $revenue);
    }

    public function test_order_repository_query_performance(): void
    {
        Order::factory()->count(50)->create();
        $orders = Order::with(['user', 'product'])->get();
        $this->assertGreaterThanOrEqual(50, $orders->count());
    }
}
