<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson('/api/health');
        
        $response->assertStatus(200);
    }

    public function test_ping_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson('/api/ping');
        
        $response->assertStatus(200)
                 ->assertJson(['message' => 'pong']);
    }

    public function test_test_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson('/api/test');
        
        $response->assertStatus(200)
                 ->assertJson(['status' => 'API is working!']);
    }
    
    public function test_public_job_listings_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson('/api/public/job-listings');
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }
}