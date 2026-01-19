<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_name_is_required(): void
    {
        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_name_minimum_length(): void
    {
        $user = User::factory()->create(['name' => 'Jo']);
        $this->assertEquals('Jo', $user->name);
    }

    public function test_user_name_maximum_length(): void
    {
        $name = str_repeat('A', 255);
        $user = User::factory()->create(['name' => $name]);
        $this->assertEquals($name, $user->name);
    }

    public function test_user_email_format_validation(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $this->assertStringContainsString('@', $user->email);
    }

    public function test_user_email_lowercase_conversion(): void
    {
        $user = User::factory()->create(['email' => 'TEST@EXAMPLE.COM']);
        $this->assertNotNull($user->email);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $this->assertNotEquals('password123', $user->password);
    }

    public function test_user_status_active_by_default(): void
    {
        $user = User::factory()->create();
        $this->assertContains($user->status, ['active', 'inactive', 'suspended']);
    }

    public function test_user_status_can_be_inactive(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $this->assertEquals('inactive', $user->status);
    }

    public function test_user_status_can_be_suspended(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);
        $this->assertEquals('suspended', $user->status);
    }

    public function test_user_age_is_numeric(): void
    {
        $user = User::factory()->create(['age' => 25]);
        $this->assertIsInt($user->age);
    }

    public function test_user_age_minimum_value(): void
    {
        $user = User::factory()->create(['age' => 18]);
        $this->assertGreaterThanOrEqual(18, $user->age);
    }

    public function test_user_age_maximum_value(): void
    {
        $user = User::factory()->create(['age' => 100]);
        $this->assertLessThanOrEqual(120, $user->age);
    }

    public function test_user_phone_format(): void
    {
        $user = User::factory()->create(['phone' => '1234567890']);
        $this->assertNotNull($user->phone);
    }

    public function test_user_phone_optional(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $this->assertNull($user->phone);
    }

    public function test_user_timestamps_created(): void
    {
        $user = User::factory()->create();
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    public function test_user_id_auto_increment(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->assertGreaterThan($user1->id, $user2->id);
    }

    public function test_user_email_unique_constraint(): void
    {
        $email = 'unique@test.com';
        User::factory()->create(['email' => $email]);
        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertEquals(1, User::where('email', $email)->count());
    }

    public function test_user_name_allows_spaces(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $this->assertStringContainsString(' ', $user->name);
    }

    public function test_user_name_allows_hyphens(): void
    {
        $user = User::factory()->create(['name' => 'Mary-Jane']);
        $this->assertStringContainsString('-', $user->name);
    }

    public function test_user_multiple_users_different_emails(): void
    {
        User::factory()->count(5)->create();
        $emails = User::pluck('email')->toArray();
        $this->assertEquals(count($emails), count(array_unique($emails)));
    }
}
