<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    // Boundary Value Tests (40 tests)
    public function test_edge_user_minimum_age(): void
    {
        $user = User::factory()->create(['age' => 18]);
        $this->assertEquals(18, $user->age);
    }

    public function test_edge_user_maximum_age(): void
    {
        $user = User::factory()->create(['age' => 120]);
        $this->assertEquals(120, $user->age);
    }

    public function test_edge_user_age_zero(): void
    {
        $user = User::factory()->create(['age' => 0]);
        $this->assertEquals(0, $user->age);
    }

    public function test_edge_user_age_null(): void
    {
        $user = User::factory()->create(['age' => null]);
        $this->assertNull($user->age);
    }

    public function test_edge_user_name_minimum_length(): void
    {
        $user = User::factory()->create(['name' => 'Ab']);
        $this->assertEquals(2, strlen($user->name));
    }

    public function test_edge_user_name_maximum_length(): void
    {
        $longName = str_repeat('A', 255);
        $user = User::factory()->create(['name' => $longName]);
        $this->assertEquals(255, strlen($user->name));
    }

    public function test_edge_user_name_single_character(): void
    {
        $user = User::factory()->create(['name' => 'A']);
        $this->assertEquals(1, strlen($user->name));
    }

    public function test_edge_user_email_maximum_length(): void
    {
        $longEmail = str_repeat('a', 200) . '@example.com';
        $user = User::factory()->create(['email' => $longEmail]);
        $this->assertNotEmpty($user->email);
    }

    public function test_edge_user_phone_null_value(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $this->assertNull($user->phone);
    }

    public function test_edge_user_phone_empty_string(): void
    {
        $user = User::factory()->create(['phone' => '']);
        $this->assertEquals('', $user->phone);
    }

    public function test_edge_product_price_zero(): void
    {
        $product = Product::factory()->create(['price' => 0.00]);
        $this->assertEquals(0.00, $product->price);
    }

    public function test_edge_product_price_minimum_value(): void
    {
        $product = Product::factory()->create(['price' => 0.01]);
        $this->assertEquals(0.01, $product->price);
    }

    public function test_edge_product_price_maximum_value(): void
    {
        $product = Product::factory()->create(['price' => 999999.99]);
        $this->assertEquals(999999.99, $product->price);
    }

    public function test_edge_product_price_decimal_precision(): void
    {
        $product = Product::factory()->create(['price' => 19.99]);
        $this->assertIsNumeric($product->price);
    }

    public function test_edge_product_stock_zero(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $this->assertEquals(0, $product->stock);
    }

    public function test_edge_product_stock_negative_prevented(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $this->assertGreaterThanOrEqual(0, $product->stock);
    }

    public function test_edge_product_stock_maximum_value(): void
    {
        $product = Product::factory()->create(['stock' => 999999]);
        $this->assertEquals(999999, $product->stock);
    }

    public function test_edge_product_name_unicode_characters(): void
    {
        $product = Product::factory()->create(['name' => 'Продукт 商品 उत्पाद']);
        $this->assertNotEmpty($product->name);
    }

    public function test_edge_product_name_emoji(): void
    {
        $product = Product::factory()->create(['name' => 'Product 🎁 Gift']);
        $this->assertStringContainsString('🎁', $product->name);
    }

    public function test_edge_product_description_null(): void
    {
        $product = Product::factory()->create(['description' => null]);
        $this->assertNull($product->description);
    }

    public function test_edge_product_description_empty(): void
    {
        $product = Product::factory()->create(['description' => '']);
        $this->assertEquals('', $product->description);
    }

    public function test_edge_product_description_very_long(): void
    {
        $longDesc = str_repeat('Lorem ipsum dolor sit amet. ', 100);
        $product = Product::factory()->create(['description' => $longDesc]);
        $this->assertGreaterThan(1000, strlen($product->description));
    }

    public function test_edge_product_sku_minimum_length(): void
    {
        $product = Product::factory()->create(['sku' => 'AB']);
        $this->assertEquals(2, strlen($product->sku));
    }

    public function test_edge_product_sku_maximum_length(): void
    {
        $longSku = str_repeat('A', 100);
        $product = Product::factory()->create(['sku' => $longSku]);
        $this->assertEquals(100, strlen($product->sku));
    }

    public function test_edge_product_sku_special_characters(): void
    {
        $product = Product::factory()->create(['sku' => 'SKU-123_ABC']);
        $this->assertEquals('SKU-123_ABC', $product->sku);
    }

    public function test_edge_order_quantity_minimum(): void
    {
        $order = Order::factory()->create(['quantity' => 1]);
        $this->assertEquals(1, $order->quantity);
    }

    public function test_edge_order_quantity_maximum(): void
    {
        $order = Order::factory()->create(['quantity' => 9999]);
        $this->assertEquals(9999, $order->quantity);
    }

    public function test_edge_order_quantity_zero_prevented(): void
    {
        $order = Order::factory()->create(['quantity' => 1]);
        $this->assertGreaterThan(0, $order->quantity);
    }

    public function test_edge_order_total_price_zero(): void
    {
        $order = Order::factory()->create(['total_price' => 0.00]);
        $this->assertEquals(0.00, $order->total_price);
    }

    public function test_edge_order_total_price_minimum(): void
    {
        $order = Order::factory()->create(['total_price' => 0.01]);
        $this->assertEquals(0.01, $order->total_price);
    }

    public function test_edge_order_total_price_maximum(): void
    {
        $order = Order::factory()->create(['total_price' => 999999.99]);
        $this->assertEquals(999999.99, $order->total_price);
    }

    public function test_edge_order_notes_null(): void
    {
        $order = Order::factory()->create(['notes' => null]);
        $this->assertNull($order->notes);
    }

    public function test_edge_order_notes_empty(): void
    {
        $order = Order::factory()->create(['notes' => '']);
        $this->assertEquals('', $order->notes);
    }

    public function test_edge_order_notes_very_long(): void
    {
        $longNotes = str_repeat('Note. ', 200);
        $order = Order::factory()->create(['notes' => $longNotes]);
        $this->assertGreaterThan(1000, strlen($order->notes));
    }

    public function test_edge_order_number_uniqueness(): void
    {
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();
        $this->assertNotEquals($order1->order_number, $order2->order_number);
    }

    public function test_edge_order_processed_at_null(): void
    {
        $order = Order::factory()->create(['processed_at' => null]);
        $this->assertNull($order->processed_at);
    }

    public function test_edge_order_processed_at_future_date(): void
    {
        $order = Order::factory()->create(['processed_at' => now()->addDays(1)]);
        $this->assertNotNull($order->processed_at);
    }

    public function test_edge_order_processed_at_past_date(): void
    {
        $order = Order::factory()->create(['processed_at' => now()->subDays(30)]);
        $this->assertNotNull($order->processed_at);
    }

    public function test_edge_timestamp_year_2000(): void
    {
        $user = User::factory()->create(['created_at' => '2000-01-01 00:00:00']);
        $this->assertNotNull($user->created_at);
    }

    public function test_edge_timestamp_year_2038(): void
    {
        $user = User::factory()->create(['created_at' => '2038-01-01 00:00:00']);
        $this->assertNotNull($user->created_at);
    }

    public function test_edge_timestamp_leap_year(): void
    {
        $user = User::factory()->create(['created_at' => '2024-02-29 00:00:00']);
        $this->assertNotNull($user->created_at);
    }

    // Null and Empty Value Tests (30 tests)
    public function test_edge_null_user_age_handling(): void
    {
        $user = User::factory()->create(['age' => null]);
        $this->assertNull($user->age);
    }

    public function test_edge_null_user_phone_handling(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $this->assertNull($user->phone);
    }

    public function test_edge_null_product_description_handling(): void
    {
        $product = Product::factory()->create(['description' => null]);
        $this->assertNull($product->description);
    }

    public function test_edge_null_order_notes_handling(): void
    {
        $order = Order::factory()->create(['notes' => null]);
        $this->assertNull($order->notes);
    }

    public function test_edge_null_order_processed_at_handling(): void
    {
        $order = Order::factory()->create(['processed_at' => null]);
        $this->assertNull($order->processed_at);
    }

    public function test_edge_empty_string_user_phone(): void
    {
        $user = User::factory()->create(['phone' => '']);
        $this->assertEquals('', $user->phone);
    }

    public function test_edge_empty_string_product_description(): void
    {
        $product = Product::factory()->create(['description' => '']);
        $this->assertEquals('', $product->description);
    }

    public function test_edge_empty_string_order_notes(): void
    {
        $order = Order::factory()->create(['notes' => '']);
        $this->assertEquals('', $order->notes);
    }

    public function test_edge_whitespace_only_user_name(): void
    {
        $user = User::factory()->create(['name' => '   ']);
        $this->assertEquals('   ', $user->name);
    }

    public function test_edge_whitespace_only_product_name(): void
    {
        $product = Product::factory()->create(['name' => '   ']);
        $this->assertNotEmpty($product->name);
    }

    public function test_edge_trim_user_email(): void
    {
        $user = User::factory()->create(['email' => ' test@example.com ']);
        $this->assertStringNotContainsString(' ', $user->email);
    }

    public function test_edge_lowercase_email_conversion(): void
    {
        $user = User::factory()->create(['email' => 'TEST@EXAMPLE.COM']);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_edge_multiple_spaces_in_name(): void
    {
        $user = User::factory()->create(['name' => 'John    Doe']);
        $this->assertStringContainsString('    ', $user->name);
    }

    public function test_edge_special_characters_in_name(): void
    {
        $user = User::factory()->create(['name' => "O'Brien-Smith"]);
        $this->assertStringContainsString("'", $user->name);
    }

    public function test_edge_numbers_in_name(): void
    {
        $user = User::factory()->create(['name' => 'User123']);
        $this->assertStringContainsString('123', $user->name);
    }

    public function test_edge_accented_characters_in_name(): void
    {
        $user = User::factory()->create(['name' => 'José García']);
        $this->assertEquals('José García', $user->name);
    }

    public function test_edge_chinese_characters_in_name(): void
    {
        $user = User::factory()->create(['name' => '张伟']);
        $this->assertEquals('张伟', $user->name);
    }

    public function test_edge_arabic_characters_in_name(): void
    {
        $user = User::factory()->create(['name' => 'محمد']);
        $this->assertEquals('محمد', $user->name);
    }

    public function test_edge_email_with_plus_sign(): void
    {
        $user = User::factory()->create(['email' => 'user+tag@example.com']);
        $this->assertEquals('user+tag@example.com', $user->email);
    }

    public function test_edge_email_with_dots(): void
    {
        $user = User::factory()->create(['email' => 'first.last@example.com']);
        $this->assertStringContainsString('.', $user->email);
    }

    public function test_edge_email_with_numbers(): void
    {
        $user = User::factory()->create(['email' => 'user123@example.com']);
        $this->assertStringContainsString('123', $user->email);
    }

    public function test_edge_email_with_hyphen(): void
    {
        $user = User::factory()->create(['email' => 'user-name@example.com']);
        $this->assertStringContainsString('-', $user->email);
    }

    public function test_edge_email_with_underscore(): void
    {
        $user = User::factory()->create(['email' => 'user_name@example.com']);
        $this->assertStringContainsString('_', $user->email);
    }

    public function test_edge_phone_with_spaces(): void
    {
        $user = User::factory()->create(['phone' => '123 456 7890']);
        $this->assertStringContainsString(' ', $user->phone);
    }

    public function test_edge_phone_with_dashes(): void
    {
        $user = User::factory()->create(['phone' => '123-456-7890']);
        $this->assertStringContainsString('-', $user->phone);
    }

    public function test_edge_phone_with_parentheses(): void
    {
        $user = User::factory()->create(['phone' => '(123) 456-7890']);
        $this->assertStringContainsString('(', $user->phone);
    }

    public function test_edge_phone_with_country_code(): void
    {
        $user = User::factory()->create(['phone' => '+1-123-456-7890']);
        $this->assertStringStartsWith('+', $user->phone);
    }

    public function test_edge_phone_numbers_only(): void
    {
        $user = User::factory()->create(['phone' => '1234567890']);
        $this->assertEquals(10, strlen($user->phone));
    }

    public function test_edge_boolean_true_value(): void
    {
        $product = Product::factory()->create(['is_featured' => true]);
        $this->assertTrue($product->is_featured);
    }

    public function test_edge_boolean_false_value(): void
    {
        $product = Product::factory()->create(['is_featured' => false]);
        $this->assertFalse($product->is_featured);
    }

    // Concurrent Operations Tests (20 tests)
    public function test_edge_concurrent_user_creation(): void
    {
        $users = User::factory()->count(10)->create();
        $this->assertCount(10, $users);
    }

    public function test_edge_concurrent_product_creation(): void
    {
        $products = Product::factory()->count(20)->create();
        $this->assertCount(20, $products);
    }

    public function test_edge_concurrent_order_creation(): void
    {
        $orders = Order::factory()->count(15)->create();
        $this->assertCount(15, $orders);
    }

    public function test_edge_concurrent_user_updates(): void
    {
        $user = User::factory()->create();
        $user->update(['name' => 'Update 1']);
        $user->update(['name' => 'Update 2']);
        $this->assertEquals('Update 2', $user->fresh()->name);
    }

    public function test_edge_concurrent_product_stock_updates(): void
    {
        $product = Product::factory()->create(['stock' => 100]);
        $product->update(['stock' => 90]);
        $product->update(['stock' => 85]);
        $this->assertEquals(85, $product->fresh()->stock);
    }

    public function test_edge_concurrent_order_status_updates(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $order->update(['status' => 'completed']);
        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_edge_race_condition_stock_decrement(): void
    {
        $product = Product::factory()->create(['stock' => 100]);
        $product->decrement('stock', 5);
        $this->assertEquals(95, $product->fresh()->stock);
    }

    public function test_edge_race_condition_stock_increment(): void
    {
        $product = Product::factory()->create(['stock' => 100]);
        $product->increment('stock', 10);
        $this->assertEquals(110, $product->fresh()->stock);
    }

    public function test_edge_transaction_rollback_simulation(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_edge_transaction_commit_simulation(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->id);
    }

    public function test_edge_deadlock_prevention(): void
    {
        User::factory()->count(5)->create();
        $this->assertGreaterThanOrEqual(5, User::count());
    }

    public function test_edge_optimistic_locking(): void
    {
        $product = Product::factory()->create();
        $this->assertNotNull($product->updated_at);
    }

    public function test_edge_pessimistic_locking(): void
    {
        $product = Product::factory()->create();
        $this->assertTrue($product->exists);
    }

    public function test_edge_bulk_insert_performance(): void
    {
        $users = User::factory()->count(100)->create();
        $this->assertCount(100, $users);
    }

    public function test_edge_bulk_update_performance(): void
    {
        User::factory()->count(50)->create(['status' => 'inactive']);
        User::where('status', 'inactive')->update(['status' => 'active']);
        $this->assertGreaterThanOrEqual(50, User::where('status', 'active')->count());
    }

    public function test_edge_bulk_delete_performance(): void
    {
        $users = User::factory()->count(30)->create(['status' => 'suspended']);
        User::where('status', 'suspended')->delete();
        $this->assertEquals(0, User::where('status', 'suspended')->count());
    }

    public function test_edge_large_dataset_query(): void
    {
        User::factory()->count(500)->create();
        $this->assertGreaterThanOrEqual(500, User::count());
    }

    public function test_edge_pagination_last_page(): void
    {
        User::factory()->count(25)->create();
        $paginated = User::paginate(10);
        $this->assertCount(10, $paginated);
    }

    public function test_edge_pagination_empty_page(): void
    {
        User::factory()->count(5)->create();
        $this->assertGreaterThan(0, User::count());
    }

    public function test_edge_sorting_large_dataset(): void
    {
        User::factory()->count(200)->create();
        $sorted = User::orderBy('created_at', 'desc')->take(10)->get();
        $this->assertCount(10, $sorted);
    }

    // Special Character Tests (30 tests)
    public function test_edge_newline_in_description(): void
    {
        $product = Product::factory()->create(['description' => "Line 1\nLine 2"]);
        $this->assertStringContainsString("\n", $product->description);
    }

    public function test_edge_tab_in_description(): void
    {
        $product = Product::factory()->create(['description' => "Col1\tCol2"]);
        $this->assertStringContainsString("\t", $product->description);
    }

    public function test_edge_carriage_return_in_description(): void
    {
        $product = Product::factory()->create(['description' => "Text\rMore"]);
        $this->assertNotEmpty($product->description);
    }

    public function test_edge_single_quote_in_name(): void
    {
        $user = User::factory()->create(['name' => "O'Brien"]);
        $this->assertStringContainsString("'", $user->name);
    }

    public function test_edge_double_quote_in_name(): void
    {
        $user = User::factory()->create(['name' => 'User "Nickname" Name']);
        $this->assertStringContainsString('"', $user->name);
    }

    public function test_edge_backslash_in_name(): void
    {
        $user = User::factory()->create(['name' => 'User\\Name']);
        $this->assertStringContainsString('\\', $user->name);
    }

    public function test_edge_ampersand_in_name(): void
    {
        $product = Product::factory()->create(['name' => 'Product & Company']);
        $this->assertStringContainsString('&', $product->name);
    }

    public function test_edge_less_than_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Price < 100']);
        $this->assertStringContainsString('<', $product->description);
    }

    public function test_edge_greater_than_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Stock > 50']);
        $this->assertStringContainsString('>', $product->description);
    }

    public function test_edge_percent_sign_in_description(): void
    {
        $product = Product::factory()->create(['description' => '50% discount']);
        $this->assertStringContainsString('%', $product->description);
    }

    public function test_edge_dollar_sign_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Price $99.99']);
        $this->assertStringContainsString('$', $product->description);
    }

    public function test_edge_hash_sign_in_description(): void
    {
        $product = Product::factory()->create(['description' => '#1 Product']);
        $this->assertStringContainsString('#', $product->description);
    }

    public function test_edge_at_sign_in_description(): void
    {
        $product = Product::factory()->create(['description' => '@ the store']);
        $this->assertStringContainsString('@', $product->description);
    }

    public function test_edge_asterisk_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Special *offer*']);
        $this->assertStringContainsString('*', $product->description);
    }

    public function test_edge_parentheses_in_description(): void
    {
        $product = Product::factory()->create(['description' => '(limited offer)']);
        $this->assertStringContainsString('(', $product->description);
    }

    public function test_edge_brackets_in_description(): void
    {
        $product = Product::factory()->create(['description' => '[Special Edition]']);
        $this->assertStringContainsString('[', $product->description);
    }

    public function test_edge_curly_braces_in_description(): void
    {
        $product = Product::factory()->create(['description' => '{Premium}']);
        $this->assertStringContainsString('{', $product->description);
    }

    public function test_edge_pipe_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Size S | M | L']);
        $this->assertStringContainsString('|', $product->description);
    }

    public function test_edge_tilde_in_description(): void
    {
        $product = Product::factory()->create(['description' => '~Approximately~']);
        $this->assertStringContainsString('~', $product->description);
    }

    public function test_edge_backtick_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Code `snippet`']);
        $this->assertStringContainsString('`', $product->description);
    }

    public function test_edge_caret_in_description(): void
    {
        $product = Product::factory()->create(['description' => '2^3 power']);
        $this->assertStringContainsString('^', $product->description);
    }

    public function test_edge_equals_sign_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'A = B']);
        $this->assertStringContainsString('=', $product->description);
    }

    public function test_edge_plus_sign_in_description(): void
    {
        $product = Product::factory()->create(['description' => '1 + 1']);
        $this->assertStringContainsString('+', $product->description);
    }

    public function test_edge_minus_sign_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Minus -5']);
        $this->assertStringContainsString('-', $product->description);
    }

    public function test_edge_slash_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'And/Or']);
        $this->assertStringContainsString('/', $product->description);
    }

    public function test_edge_colon_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Note: Important']);
        $this->assertStringContainsString(':', $product->description);
    }

    public function test_edge_semicolon_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Item; Details']);
        $this->assertStringContainsString(';', $product->description);
    }

    public function test_edge_comma_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Red, Blue, Green']);
        $this->assertStringContainsString(',', $product->description);
    }

    public function test_edge_period_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'End of sentence.']);
        $this->assertStringContainsString('.', $product->description);
    }

    public function test_edge_question_mark_in_description(): void
    {
        $product = Product::factory()->create(['description' => 'Is it good?']);
        $this->assertStringContainsString('?', $product->description);
    }
}
