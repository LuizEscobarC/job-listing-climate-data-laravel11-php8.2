<?php

namespace Tests\Feature;

use App\Enums\JobType;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobListingFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        
        JobListing::factory()->count(5)->create([
            'user_id' => $user->id,
            'title' => 'PHP Developer',
            'company_name' => 'Tech Solutions',
            'location' => 'Remote',
            'type' => JobType::CLT->value,
            'salary' => 80000,
            'experience_level' => 'senior',
        ]);
        
        JobListing::factory()->count(3)->create([
            'user_id' => $user->id,
            'title' => 'Frontend Developer',
            'company_name' => 'Creative Agency',
            'location' => 'New York',
            'type' => JobType::PJ->value,
            'salary' => 60000,
            'experience_level' => 'mid',
        ]);
        
        JobListing::factory()->count(2)->create([
            'user_id' => $user->id,
            'title' => 'Junior Developer',
            'company_name' => 'Startup Inc',
            'location' => 'San Francisco',
            'type' => JobType::FREELANCER->value,
            'salary' => 40000,
            'experience_level' => 'junior',
        ]);
    }

    /** @test */
    public function it_can_filter_by_title()
    {
        $response = $this->getJson('/api/public/job-listings?title=PHP');
        
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }
    
    /** @test */
    public function it_can_filter_by_company_name()
    {
        $response = $this->getJson('/api/public/job-listings?companyName=Tech');
        
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }
    
    /** @test */
    public function it_can_filter_by_location()
    {
        $response = $this->getJson('/api/public/job-listings?location=Remote');
        
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }
    
    /** @test */
    public function it_can_filter_by_job_type()
    {
        $response = $this->getJson('/api/public/job-listings?type=PJ');
        
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }
    
    /** @test */
    public function it_can_filter_by_salary_range()
    {
        $response = $this->getJson('/api/public/job-listings?salaryMin=50000&salaryMax=70000');
        
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }
    
    /** @test */
    public function it_can_filter_by_experience_level()
    {
        $response = $this->getJson('/api/public/job-listings?experienceLevel=senior');
        
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }
    
    /** @test */
    public function it_can_search_across_multiple_fields()
    {
        $response = $this->getJson('/api/public/job-listings?search=Developer');
        
        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
    }
    
    /** @test */
    public function it_can_sort_results()
    {
        $response = $this->getJson('/api/public/job-listings?order_by=salary&order_direction=desc');
        
        $response->assertStatus(200);
        $this->assertEquals(80000, $response->json('data.0.salary'));
    }
}
