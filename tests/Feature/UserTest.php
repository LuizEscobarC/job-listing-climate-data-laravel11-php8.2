<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user for authenticated requests
        $this->adminUser = User::factory()->create([
            'role' => UserRole::RECRUITER,
            'is_active' => true,
        ]);
        
        $this->adminToken = $this->adminUser->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function user_can_be_created()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::CANDIDATE->value,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/users', $userData);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => UserRole::CANDIDATE->value,
        ]);
    }

    /** @test */
    public function user_can_be_retrieved()
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $user->id,
                     'name' => $user->name,
                     'email' => $user->email,
                 ]);
    }

    /** @test */
    public function users_can_be_listed()
    {
        User::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'email', 'role']
                     ],
                     'links',
                     'meta'
                 ]);
    }

    /** @test */
    public function user_can_be_updated()
    {
        $user = User::factory()->create();
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function user_can_be_soft_deleted()
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);
        
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    /** @test */
    public function user_status_can_be_toggled()
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->patchJson("/api/users/{$user->id}/toggle-status", [
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function user_can_be_restored()
    {
        $user = User::factory()->create();
        $user->delete(); // Soft delete

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->patchJson("/api/users/{$user->id}/restore");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function users_can_be_filtered_by_role()
    {
        User::factory()->create(['role' => UserRole::CANDIDATE]);
        User::factory()->create(['role' => UserRole::RECRUITER]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/users?role=candidate');

        $response->assertStatus(200);
        
        $users = $response->json('data');
        $this->assertNotEmpty($users);
        
        foreach ($users as $user) {
            $this->assertEquals('candidate', $user['role']);
        }
    }

    /** @test */
    public function users_can_be_searched_by_name()
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/users?search=John');

        $response->assertStatus(200);
        
        $users = $response->json('data');
        $this->assertNotEmpty($users);
        $this->assertStringContainsString('John', $users[0]['name']);
    }
}
