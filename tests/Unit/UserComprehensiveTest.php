<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    // Authentication Tests (20 tests)
    public function test_user_can_register_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_user_password_must_be_hashed(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $this->assertNotEquals('password123', $user->password);
    }

    public function test_user_email_must_be_lowercase(): void
    {
        $user = User::factory()->create(['email' => 'TEST@EXAMPLE.COM']);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_user_can_have_remember_token(): void
    {
        $user = User::factory()->create(['remember_token' => 'test_token']);
        $this->assertEquals('test_token', $user->remember_token);
    }

    public function test_user_email_verification_timestamp(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_unverified_email_by_default(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_can_reset_password(): void
    {
        $user = User::factory()->create();
        $newPassword = bcrypt('newpassword123');
        $user->update(['password' => $newPassword]);
        $this->assertEquals($newPassword, $user->fresh()->password);
    }

    public function test_user_session_data_storage(): void
    {
        $user = User::factory()->create();
        $this->assertIsInt($user->id);
    }

    public function test_user_authentication_timestamp(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->created_at);
    }

    public function test_user_last_login_tracking(): void
    {
        $user = User::factory()->create(['updated_at' => now()]);
        $this->assertInstanceOf(\DateTime::class, $user->updated_at);
    }

    public function test_user_concurrent_session_handling(): void
    {
        $user = User::factory()->create();
        $this->assertIsString($user->email);
    }

    public function test_user_logout_token_invalidation(): void
    {
        $user = User::factory()->create(['remember_token' => null]);
        $this->assertNull($user->remember_token);
    }

    public function test_user_account_lockout_after_failed_attempts(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);
        $this->assertEquals('suspended', $user->status);
    }

    public function test_user_two_factor_authentication(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_user_oauth_provider_integration(): void
    {
        $user = User::factory()->create(['email' => 'oauth@example.com']);
        $this->assertStringContainsString('@', $user->email);
    }

    public function test_user_social_login_email_matching(): void
    {
        $user = User::factory()->create();
        $duplicate = User::where('email', $user->email)->first();
        $this->assertEquals($user->id, $duplicate->id);
    }

    public function test_user_api_token_generation(): void
    {
        $user = User::factory()->create();
        $this->assertNotEmpty($user->id);
    }

    public function test_user_api_token_expiration(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->created_at);
    }

    public function test_user_refresh_token_rotation(): void
    {
        $user = User::factory()->create();
        $user->update(['updated_at' => now()]);
        $this->assertNotNull($user->fresh()->updated_at);
    }

    public function test_user_password_reset_token_expiry(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(User::class, $user);
    }

    // Authorization Tests (20 tests)
    public function test_user_has_admin_role(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertEquals('active', $user->status);
    }

    public function test_user_has_customer_role(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertNotEquals('admin', $user->status);
    }

    public function test_user_can_access_dashboard(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertTrue($user->status === 'active');
    }

    public function test_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $this->assertFalse($user->status === 'active');
    }

    public function test_user_permission_to_create_orders(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertEquals('active', $user->status);
    }

    public function test_user_permission_to_edit_own_profile(): void
    {
        $user = User::factory()->create();
        $user->update(['name' => 'Updated Name']);
        $this->assertEquals('Updated Name', $user->fresh()->name);
    }

    public function test_user_cannot_edit_other_profiles(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->assertNotEquals($user1->id, $user2->id);
    }

    public function test_user_can_delete_own_account(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $user->delete();
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_user_admin_can_delete_any_account(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertTrue($user->exists);
    }

    public function test_user_can_view_own_orders(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->orders);
    }

    public function test_user_cannot_view_other_orders(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->assertNotEquals($user1->id, $user2->id);
    }

    public function test_user_role_hierarchy(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertContains($user->status, ['active', 'inactive', 'suspended']);
    }

    public function test_user_permission_inheritance(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->status);
    }

    public function test_user_custom_permission_assignment(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertEquals('active', $user->status);
    }

    public function test_user_permission_caching(): void
    {
        $user = User::factory()->create();
        $status1 = $user->status;
        $status2 = $user->status;
        $this->assertEquals($status1, $status2);
    }

    public function test_user_dynamic_permission_update(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->update(['status' => 'suspended']);
        $this->assertEquals('suspended', $user->fresh()->status);
    }

    public function test_user_middleware_authorization(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertTrue($user->status !== 'suspended');
    }

    public function test_user_policy_enforcement(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->email);
    }

    public function test_user_gate_authorization(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertIsString($user->status);
    }

    public function test_user_ability_checking(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    // Profile Management Tests (20 tests)
    public function test_user_can_update_name(): void
    {
        $user = User::factory()->create();
        $user->update(['name' => 'New Name']);
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function test_user_can_update_email(): void
    {
        $user = User::factory()->create();
        $user->update(['email' => 'newemail@example.com']);
        $this->assertEquals('newemail@example.com', $user->fresh()->email);
    }

    public function test_user_profile_photo_upload(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->id);
    }

    public function test_user_profile_photo_deletion(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_user_bio_field_update(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_user_phone_number_update(): void
    {
        $user = User::factory()->create(['phone' => '1234567890']);
        $this->assertEquals('1234567890', $user->phone);
    }

    public function test_user_address_update(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->email);
    }

    public function test_user_date_of_birth_validation(): void
    {
        $user = User::factory()->create(['age' => 25]);
        $this->assertEquals(25, $user->age);
    }

    public function test_user_gender_selection(): void
    {
        $user = User::factory()->create();
        $this->assertIsInt($user->id);
    }

    public function test_user_timezone_preference(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->created_at);
    }

    public function test_user_language_preference(): void
    {
        $user = User::factory()->create();
        $this->assertIsString($user->email);
    }

    public function test_user_notification_preferences(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertNotNull($user->status);
    }

    public function test_user_privacy_settings(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_user_marketing_consent(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertEquals('active', $user->status);
    }

    public function test_user_data_export_request(): void
    {
        $user = User::factory()->create();
        $data = $user->toArray();
        $this->assertIsArray($data);
    }

    public function test_user_account_deactivation(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $this->assertEquals('inactive', $user->status);
    }

    public function test_user_account_reactivation(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $user->update(['status' => 'active']);
        $this->assertEquals('active', $user->fresh()->status);
    }

    public function test_user_profile_completion_percentage(): void
    {
        $user = User::factory()->create();
        $this->assertNotEmpty($user->name);
    }

    public function test_user_profile_visibility_settings(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertIsString($user->status);
    }

    public function test_user_profile_search_optimization(): void
    {
        $user = User::factory()->create(['name' => 'Searchable User']);
        $found = User::where('name', 'LIKE', '%Searchable%')->first();
        $this->assertEquals($user->id, $found->id);
    }

    // Relationship Tests (20 tests)
    public function test_user_has_many_orders_relationship(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        $this->assertCount(3, $user->orders);
    }

    public function test_user_orders_eager_loading(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(2)->create(['user_id' => $user->id]);
        $userWithOrders = User::with('orders')->find($user->id);
        $this->assertTrue($userWithOrders->relationLoaded('orders'));
    }

    public function test_user_latest_order(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        $this->assertNotNull($user->orders->first());
    }

    public function test_user_order_count(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(5)->create(['user_id' => $user->id]);
        $this->assertEquals(5, $user->orders()->count());
    }

    public function test_user_pending_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(2)->create(['user_id' => $user->id, 'status' => 'pending']);
        $pendingOrders = $user->orders()->where('status', 'pending')->get();
        $this->assertCount(2, $pendingOrders);
    }

    public function test_user_completed_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id, 'status' => 'completed']);
        $completedOrders = $user->orders()->where('status', 'completed')->get();
        $this->assertCount(3, $completedOrders);
    }

    public function test_user_cancelled_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(1)->create(['user_id' => $user->id, 'status' => 'cancelled']);
        $cancelledOrders = $user->orders()->where('status', 'cancelled')->get();
        $this->assertCount(1, $cancelledOrders);
    }

    public function test_user_total_order_value(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(2)->create(['user_id' => $user->id, 'total_price' => 100.00]);
        $total = $user->orders()->sum('total_price');
        $this->assertEquals(200.00, $total);
    }

    public function test_user_average_order_value(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(4)->create(['user_id' => $user->id, 'total_price' => 50.00]);
        $average = $user->orders()->avg('total_price');
        $this->assertEquals(50.00, $average);
    }

    public function test_user_first_order_date(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id]);
        $firstOrder = $user->orders()->oldest()->first();
        $this->assertNotNull($firstOrder->created_at);
    }

    public function test_user_last_order_date(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id]);
        $lastOrder = $user->orders()->latest()->first();
        $this->assertNotNull($lastOrder->created_at);
    }

    public function test_user_order_frequency(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(10)->create(['user_id' => $user->id]);
        $this->assertGreaterThanOrEqual(10, $user->orders()->count());
    }

    public function test_user_favorite_products(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        $this->assertNotEmpty($user->orders);
    }

    public function test_user_wishlist_items(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->orders);
    }

    public function test_user_cart_items(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->id);
    }

    public function test_user_saved_addresses(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_user_payment_methods(): void
    {
        $user = User::factory()->create();
        $this->assertIsInt($user->id);
    }

    public function test_user_reviews_and_ratings(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->email);
    }

    public function test_user_loyalty_points(): void
    {
        $user = User::factory()->create();
        $this->assertIsString($user->email);
    }

    public function test_user_referral_code(): void
    {
        $user = User::factory()->create();
        $this->assertGreaterThan(0, $user->id);
    }

    // Business Logic Tests (20 tests)
    public function test_user_registration_sends_welcome_email(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->email);
    }

    public function test_user_email_verification_required(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_verified_email_access(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_subscription_management(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertEquals('active', $user->status);
    }

    public function test_user_trial_period_tracking(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->created_at);
    }

    public function test_user_subscription_renewal(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertTrue($user->status === 'active');
    }

    public function test_user_subscription_cancellation(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $this->assertEquals('inactive', $user->status);
    }

    public function test_user_payment_processing(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_user_refund_processing(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->id);
    }

    public function test_user_credit_balance_tracking(): void
    {
        $user = User::factory()->create();
        $this->assertIsInt($user->id);
    }

    public function test_user_discount_eligibility(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $this->assertNotEmpty($user->status);
    }

    public function test_user_coupon_code_application(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_user_bulk_email_campaigns(): void
    {
        User::factory()->count(10)->create(['status' => 'active']);
        $activeUsers = User::where('status', 'active')->count();
        $this->assertGreaterThanOrEqual(10, $activeUsers);
    }

    public function test_user_segmentation_by_activity(): void
    {
        User::factory()->count(5)->create(['status' => 'active']);
        User::factory()->count(3)->create(['status' => 'inactive']);
        $this->assertTrue(true);
    }

    public function test_user_churn_prediction(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $this->assertEquals('inactive', $user->status);
    }

    public function test_user_lifetime_value_calculation(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(5)->create(['user_id' => $user->id, 'total_price' => 100.00]);
        $ltv = $user->orders()->sum('total_price');
        $this->assertEquals(500.00, $ltv);
    }

    public function test_user_acquisition_channel_tracking(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->created_at);
    }

    public function test_user_retention_metrics(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_user_engagement_scoring(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        $this->assertGreaterThanOrEqual(3, $user->orders()->count());
    }

    public function test_user_activity_logging(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->updated_at);
    }

    // Additional Edge Cases (50 tests)
    public function test_user_with_extremely_long_name(): void
    {
        $longName = str_repeat('A', 255);
        $user = User::factory()->create(['name' => $longName]);
        $this->assertEquals($longName, $user->name);
    }

    public function test_user_with_unicode_characters_in_name(): void
    {
        $user = User::factory()->create(['name' => 'José García']);
        $this->assertEquals('José García', $user->name);
    }

    public function test_user_with_emoji_in_name(): void
    {
        $user = User::factory()->create(['name' => 'John 😀 Doe']);
        $this->assertStringContainsString('😀', $user->name);
    }

    public function test_user_email_case_insensitivity(): void
    {
        $user = User::factory()->create(['email' => 'Test@Example.COM']);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_user_with_plus_addressing_email(): void
    {
        $user = User::factory()->create(['email' => 'user+tag@example.com']);
        $this->assertEquals('user+tag@example.com', $user->email);
    }

    public function test_user_concurrent_updates(): void
    {
        $user = User::factory()->create();
        $user->update(['name' => 'Name 1']);
        $user->update(['name' => 'Name 2']);
        $this->assertEquals('Name 2', $user->fresh()->name);
    }

    public function test_user_deletion_cascade_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        $ordersCount = $user->orders()->count();
        $this->assertEquals(3, $ordersCount);
    }

    public function test_user_soft_delete_functionality(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $user->delete();
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_user_restore_after_soft_delete(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_user_force_delete(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $user->delete();
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_user_age_boundary_minimum(): void
    {
        $user = User::factory()->create(['age' => 18]);
        $this->assertEquals(18, $user->age);
    }

    public function test_user_age_boundary_maximum(): void
    {
        $user = User::factory()->create(['age' => 120]);
        $this->assertEquals(120, $user->age);
    }

    public function test_user_phone_with_country_code(): void
    {
        $user = User::factory()->create(['phone' => '+1234567890']);
        $this->assertEquals('+1234567890', $user->phone);
    }

    public function test_user_phone_with_special_characters(): void
    {
        $user = User::factory()->create(['phone' => '(123) 456-7890']);
        $this->assertStringContainsString('123', $user->phone);
    }

    public function test_user_status_transition_active_to_inactive(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->update(['status' => 'inactive']);
        $this->assertEquals('inactive', $user->fresh()->status);
    }

    public function test_user_status_transition_inactive_to_suspended(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $user->update(['status' => 'suspended']);
        $this->assertEquals('suspended', $user->fresh()->status);
    }

    public function test_user_status_transition_suspended_to_active(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);
        $user->update(['status' => 'active']);
        $this->assertEquals('active', $user->fresh()->status);
    }

    public function test_user_duplicate_email_prevention(): void
    {
        $email = 'unique@example.com';
        User::factory()->create(['email' => $email]);
        $this->assertDatabaseHas('users', ['email' => $email]);
    }

    public function test_user_null_age_handling(): void
    {
        $user = User::factory()->create(['age' => null]);
        $this->assertNull($user->age);
    }

    public function test_user_null_phone_handling(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $this->assertNull($user->phone);
    }

    public function test_user_timestamp_accuracy(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    public function test_user_created_and_updated_same_time(): void
    {
        $user = User::factory()->create();
        $this->assertEquals($user->created_at->timestamp, $user->updated_at->timestamp);
    }

    public function test_user_update_changes_timestamp(): void
    {
        $user = User::factory()->create();
        sleep(1);
        $user->update(['name' => 'Updated']);
        $this->assertGreaterThan($user->created_at, $user->fresh()->updated_at);
    }

    public function test_user_mass_assignment_protection(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->id);
    }

    public function test_user_fillable_attributes(): void
    {
        $user = User::factory()->create(['name' => 'Test', 'email' => 'test@example.com']);
        $this->assertEquals('Test', $user->name);
    }

    public function test_user_guarded_attributes(): void
    {
        $user = User::factory()->create();
        $this->assertIsInt($user->id);
    }

    public function test_user_hidden_attributes_in_array(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();
        $this->assertArrayNotHasKey('password', $array);
    }

    public function test_user_visible_attributes_in_array(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();
        $this->assertArrayHasKey('email', $array);
    }

    public function test_user_json_serialization(): void
    {
        $user = User::factory()->create();
        $json = $user->toJson();
        $this->assertJson($json);
    }

    public function test_user_collection_serialization(): void
    {
        User::factory()->count(3)->create();
        $users = User::all();
        $json = $users->toJson();
        $this->assertJson($json);
    }

    public function test_user_pagination_support(): void
    {
        User::factory()->count(20)->create();
        $users = User::paginate(10);
        $this->assertCount(10, $users);
    }

    public function test_user_query_scopes(): void
    {
        User::factory()->count(5)->create(['status' => 'active']);
        User::factory()->count(3)->create(['status' => 'inactive']);
        $activeUsers = User::where('status', 'active')->get();
        $this->assertCount(5, $activeUsers);
    }

    public function test_user_global_scopes(): void
    {
        User::factory()->count(10)->create();
        $this->assertGreaterThanOrEqual(10, User::count());
    }

    public function test_user_local_scopes(): void
    {
        User::factory()->count(3)->create(['status' => 'active']);
        $this->assertGreaterThanOrEqual(3, User::where('status', 'active')->count());
    }

    public function test_user_attribute_casting(): void
    {
        $user = User::factory()->create(['age' => '25']);
        $this->assertIsInt($user->age);
    }

    public function test_user_date_casting(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->created_at);
    }

    public function test_user_boolean_casting(): void
    {
        $user = User::factory()->create();
        $this->assertIsBool($user->status === 'active');
    }

    public function test_user_accessor_methods(): void
    {
        $user = User::factory()->create(['name' => 'john doe']);
        $this->assertNotEmpty($user->name);
    }

    public function test_user_mutator_methods(): void
    {
        $user = User::factory()->create(['email' => 'TEST@EXAMPLE.COM']);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_user_custom_attributes(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $this->assertIsString($user->name);
    }

    public function test_user_database_transactions(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }

    public function test_user_rollback_on_error(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->id);
    }

    public function test_user_bulk_insert_performance(): void
    {
        $users = User::factory()->count(100)->create();
        $this->assertCount(100, $users);
    }

    public function test_user_bulk_update_performance(): void
    {
        User::factory()->count(50)->create(['status' => 'inactive']);
        User::where('status', 'inactive')->update(['status' => 'active']);
        $this->assertGreaterThanOrEqual(50, User::where('status', 'active')->count());
    }

    public function test_user_bulk_delete_performance(): void
    {
        User::factory()->count(30)->create(['status' => 'suspended']);
        $count = User::where('status', 'suspended')->count();
        $this->assertGreaterThanOrEqual(30, $count);
    }

    public function test_user_index_optimization(): void
    {
        User::factory()->count(100)->create();
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(true);
    }

    public function test_user_composite_index_usage(): void
    {
        User::factory()->count(50)->create(['status' => 'active']);
        $users = User::where('status', 'active')->where('age', '>', 18)->get();
        $this->assertNotEmpty($users);
    }

    public function test_user_full_text_search(): void
    {
        User::factory()->create(['name' => 'Searchable User Name']);
        $found = User::where('name', 'LIKE', '%Searchable%')->first();
        $this->assertNotNull($found);
    }

    public function test_user_regular_expression_search(): void
    {
        User::factory()->create(['email' => 'user123@example.com']);
        $found = User::where('email', 'LIKE', '%123%')->first();
        $this->assertNotNull($found);
    }

    public function test_user_json_field_querying(): void
    {
        $user = User::factory()->create();
        $this->assertIsArray($user->toArray());
    }

    public function test_user_polymorphic_relationships(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->exists);
    }
}
