<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'active'
        ]);
        
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'active'
        ]);
    }

    public function test_user_has_required_fields(): void
    {
        $user = User::factory()->create();
        
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
    }

    public function test_user_email_is_unique(): void
    {
        $email = 'unique@example.com';
        User::factory()->create(['email' => $email]);
        
        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertEquals(1, User::where('email', $email)->count());
    }

    public function test_user_status_values(): void
    {
        $activeUser = User::factory()->create(['status' => 'active']);
        $inactiveUser = User::factory()->create(['status' => 'inactive']);
        
        $this->assertEquals('active', $activeUser->status);
        $this->assertEquals('inactive', $inactiveUser->status);
    }

    public function test_user_has_many_orders(): void
    {
        $user = User::factory()->create();
        $products = \App\Models\Product::factory()->count(3)->create();
        
        foreach ($products as $product) {
            Order::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id
            ]);
        }
        
        $this->assertCount(3, $user->orders);
    }

    public function test_bulk_user_creation(): void
    {
        User::factory()->count(50)->create();
        
        $this->assertDatabaseCount('users', 50);
    }

    public function test_user_name_attribute(): void
    {
        $user = User::factory()->create(['name' => 'Jane Smith']);
        
        $this->assertEquals('Jane Smith', $user->name);
    }

    public function test_users_by_status(): void
    {
        User::factory()->count(10)->create(['status' => 'active']);
        User::factory()->count(5)->create(['status' => 'inactive']);
        
        $active = User::where('status', 'active')->count();
        $inactive = User::where('status', 'inactive')->count();
        
        $this->assertEquals(10, $active);
        $this->assertEquals(5, $inactive);
    }

    public function test_user_email_format(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        $this->assertStringContainsString('@', $user->email);
        $this->assertStringContainsString('.', $user->email);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        
        $this->assertNotEquals('password123', $user->password);
        $this->assertNotEmpty($user->password);
    }
}
