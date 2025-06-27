<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->getJson('/api/test');

        $response->assertStatus(200)
            ->assertJson(['status' => 'API is working!']);
    }
    
    public function test_api_health_endpoint(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
    }
    
    public function test_api_ping_endpoint(): void
    {
        $response = $this->getJson('/api/ping');

        $response->assertStatus(200)
            ->assertJson(['message' => 'pong']);
    }
}
