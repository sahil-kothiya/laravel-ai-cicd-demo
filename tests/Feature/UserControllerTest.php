<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_index(): void
    {
        // Create some test users
        User::factory()->count(3)->create();

        $response = $this->withoutExceptionHandling()->get('/users');
        $response->assertStatus(200);
    }

    public function test_show(): void
    {
        // Create a test user
        $user = User::factory()->create();

        $response = $this->get('/users/'.$user->id);
        $response->assertStatus(200);
    }

    public function test_update(): void
    {
        // Create a test user
        $user = User::factory()->create();

        $response = $this->put('/users/'.$user->id, [
            'name' => 'Updated Name',
        ]);
        $response->assertStatus(200);
    }
}
