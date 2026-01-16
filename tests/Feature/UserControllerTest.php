<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test user index with pagination
     * Simulates heavy database query with multiple records
     */
    public function test_index_returns_paginated_users(): void
    {
        // Create 50 users to simulate heavy load
        User::factory()->count(50)->create();
        
        $response = $this->getJson('/users?per_page=20');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'current_page',
                         'data',
                         'per_page',
                         'total'
                     ]
                 ]);
        
        $this->assertEquals(20, count($response->json('data.data')));
    }

    /**
     * Test user index with search filter
     * Simulates complex query with LIKE operations
     */
    public function test_index_with_search_filter(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        User::factory()->count(30)->create();
        
        sleep(1); // Simulate slow operation
        
        $response = $this->getJson('/users?search=John');
        
        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertGreaterThan(0, count($data));
    }

    /**
     * Test user index with status filter
     */
    public function test_index_with_status_filter(): void
    {
        User::factory()->count(10)->create(['status' => 'active']);
        User::factory()->count(5)->create(['status' => 'inactive']);
        
        $response = $this->getJson('/users?status=active');
        
        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data.data'));
    }

    /**
     * Test maximum per_page validation
     */
    public function test_index_rejects_excessive_per_page(): void
    {
        $response = $this->getJson('/users?per_page=150');
        
        $response->assertStatus(422)
                 ->assertJson(['error' => 'Maximum per_page value is 100']);
    }

    /**
     * Test show single user
     */
    public function test_show_returns_single_user(): void
    {
        $user = User::factory()->create();
        
        $response = $this->getJson("/users/{$user->id}");
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'name',
                         'email',
                         'status'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $user->id,
                         'email' => $user->email
                     ]
                 ]);
    }

    /**
     * Test show with invalid ID
     */
    public function test_show_with_invalid_id(): void
    {
        $response = $this->getJson('/users/invalid');
        
        $response->assertStatus(422)
                 ->assertJson(['error' => 'Invalid user ID']);
    }

    /**
     * Test show non-existent user
     */
    public function test_show_returns_404_for_nonexistent_user(): void
    {
        $response = $this->getJson('/users/99999');
        
        $response->assertStatus(404)
                 ->assertJson(['error' => 'User not found']);
    }

    /**
     * Test user creation with valid data
     * Simulates database write operations
     */
    public function test_store_creates_user_with_valid_data(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
            'age' => 25,
            'phone' => '1234567890'
        ];
        
        $response = $this->postJson('/users', $userData);
        
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'name',
                         'email',
                         'status'
                     ]
                 ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'name' => 'John Doe'
        ]);
    }

    /**
     * Test user creation validation - name required
     */
    public function test_store_validation_name_required(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#'
        ];
        
        $response = $this->postJson('/users', $userData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test user creation validation - name format
     */
    public function test_store_validation_name_format(): void
    {
        $userData = [
            'name' => 'John123',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#'
        ];
        
        $response = $this->postJson('/users', $userData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test user creation validation - email required
     */
    public function test_store_validation_email_required(): void
    {
        $userData = [
            'name' => 'John Doe',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#'
        ];
        
        $response = $this->postJson('/users', $userData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user creation validation - email format
     */
    public function test_store_validation_email_format(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#'
        ];
        
        $response = $this->postJson('/users', $userData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user creation validation - email unique
     */
    public function test_store_validation_email_unique(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);
        
        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#'
        ];
        
        $response = $this->postJson('/users', $userData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user creation validation - password required
     */
    public function test_store_validation_password_required(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com'
        ];
        
        $response = $this->postJson('/users', $userData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test user creation validation - password confirmation
     */
    public function test_store_validation_password_confirmation(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'DifferentPass123!@#'
        ];
        
        $response = $this->postJson('/users', $userData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test user creation validation - age minimum
     */
    public function test_store_validation_age_minimum(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
            'age' => 15
        ];
        
        $response = $this->postJson('/users', $userData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['age']);
    }

    /**
     * Test user update with valid data
     */
    public function test_update_modifies_user_with_valid_data(): void
    {
        $user = User::factory()->create();
        
        $updateData = [
            'name' => 'Updated Name',
            'age' => 30
        ];
        
        $response = $this->putJson("/users/{$user->id}", $updateData);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'User updated successfully'
                 ]);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'age' => 30
        ]);
    }

    /**
     * Test user update with email uniqueness
     */
    public function test_update_validates_email_uniqueness(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);
        
        $updateData = [
            'email' => 'user2@example.com'
        ];
        
        $response = $this->putJson("/users/{$user1->id}", $updateData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user update with same email (should pass)
     */
    public function test_update_allows_same_email(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        
        $updateData = [
            'name' => 'New Name',
            'email' => 'user@example.com'
        ];
        
        $response = $this->putJson("/users/{$user->id}", $updateData);
        
        $response->assertStatus(200);
    }

    /**
     * Test user update non-existent user
     */
    public function test_update_returns_404_for_nonexistent_user(): void
    {
        $response = $this->putJson('/users/99999', ['name' => 'Test']);
        
        $response->assertStatus(404)
                 ->assertJson(['error' => 'User not found']);
    }

    /**
     * Test user deletion
     */
    public function test_destroy_deletes_user(): void
    {
        $user = User::factory()->create();
        
        $response = $this->deleteJson("/users/{$user->id}");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'User deleted successfully'
                 ]);
        
        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    /**
     * Test user deletion non-existent user
     */
    public function test_destroy_returns_404_for_nonexistent_user(): void
    {
        $response = $this->deleteJson('/users/99999');
        
        $response->assertStatus(404)
                 ->assertJson(['error' => 'User not found']);
    }

    /**
     * Test bulk user creation
     * This simulates heavy database operations
     */
    public function test_bulk_store_creates_multiple_users(): void
    {
        $users = [];
        for ($i = 0; $i < 20; $i++) {
            $users[] = [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => 'SecurePass123!@#'
            ];
        }
        
        sleep(2); // Simulate slow bulk operation
        
        $response = $this->postJson('/users/bulk', ['users' => $users]);
        
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message'
                 ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'user0@example.com'
        ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'user19@example.com'
        ]);
    }

    /**
     * Test bulk creation validation - empty array
     */
    public function test_bulk_store_validation_empty_array(): void
    {
        $response = $this->postJson('/users/bulk', ['users' => []]);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['users']);
    }

    /**
     * Test bulk creation validation - exceeds maximum
     */
    public function test_bulk_store_validation_exceeds_maximum(): void
    {
        $users = [];
        for ($i = 0; $i < 101; $i++) {
            $users[] = [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => 'password'
            ];
        }
        
        $response = $this->postJson('/users/bulk', ['users' => $users]);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['users']);
    }

    /**
     * Test performance with concurrent requests simulation
     */
    public function test_concurrent_user_operations(): void
    {
        // Create users
        $users = User::factory()->count(10)->create();
        
        // Simulate multiple reads
        foreach ($users as $user) {
            $response = $this->getJson("/users/{$user->id}");
            $response->assertStatus(200);
        }
        
        sleep(1); // Simulate processing time
        
        $this->assertTrue(true);
    }

    /**
     * Test complex query with multiple filters
     */
    public function test_index_with_multiple_filters(): void
    {
        User::factory()->count(15)->create(['status' => 'active']);
        User::factory()->count(10)->create(['status' => 'inactive']);
        User::factory()->create([
            'name' => 'Special User',
            'status' => 'active'
        ]);
        
        sleep(1); // Simulate slow query
        
        $response = $this->getJson('/users?status=active&search=Special');
        
        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertGreaterThan(0, count($data));
    }
}
