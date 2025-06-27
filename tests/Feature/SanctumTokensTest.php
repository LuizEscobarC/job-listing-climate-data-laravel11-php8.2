<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SanctumTokensTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_is_created_when_user_registers(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::CANDIDATE,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_token_is_created_when_user_logs_in(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_token_is_deleted_when_user_logs_out(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_sanctum_token_can_access_protected_routes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $response->assertStatus(200);
    }

    public function test_invalid_token_cannot_access_protected_routes(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/profile');

        $response->assertStatus(401);
    }

    public function test_expired_token_cannot_be_used(): void
    {
        // Configure Sanctum to use explicit expiration (1 minute)
        config(['sanctum.expiration' => 1]);
        
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Simulate time passage by forcing token expiration
        // Access the created token and set its expiration date to the past
        // Force Sanctum to verify expiration on the next request
        $tokenId = explode('|', $token)[0];
        $accessToken = PersonalAccessToken::find($tokenId);
        if (!$accessToken) {
            $this->fail('Token não encontrado.');
        }
        $accessToken->created_at = now()->subMinutes(60);
        $accessToken->save();
        
        app()->forgetInstance('auth.guard');
        app()->forgetInstance('auth');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');
        
        $response->assertStatus(401);
    }
}