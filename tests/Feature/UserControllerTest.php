<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => UserRole::RECRUITER,
            'is_active' => true,
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_index_endpoint_with_filters(): void
    {
        User::factory()->create(['name' => 'Test User 1', 'role' => UserRole::CANDIDATE]);
        User::factory()->create(['name' => 'Test User 2', 'role' => UserRole::CANDIDATE]);
        User::factory()->create(['name' => 'Another User', 'role' => UserRole::RECRUITER]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/users?role=candidate');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.role', UserRole::CANDIDATE->value);
    }

    public function test_toggle_status_endpoint(): void
    {
        $user = User::factory()->create([
            'name' => 'Active User',
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson('/api/users/' . $user->id . '/toggle-status', [
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_restore_endpoint(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson('/api/users/' . $user->id . '/restore');

        $response->assertStatus(200)
            ->assertJsonPath('message', __('messages.restored_successfully', ['resource' => __('messages.User')]));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }
}
