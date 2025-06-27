<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a recruiter user
        $this->recruiter = User::factory()->create([
            'role' => UserRole::RECRUITER,
        ]);

        // Create a token for the recruiter
        $this->recruiterToken = $this->recruiter->createToken('test-token')->plainTextToken;

        // Create a candidate user
        $this->candidate = User::factory()->create([
            'role' => UserRole::CANDIDATE,
        ]);

        // Create a token for the candidate
        $this->candidateToken = $this->candidate->createToken('test-token')->plainTextToken;
    }

    public function test_recruiter_can_create_job_listing(): void
    {
        $jobData = [
            'title' => 'Senior Developer',
            'description' => 'We are looking for an experienced developer.',
            'company_name' => 'Tech Corp',
            'location' => 'Remote',
            'type' => 'CLT',
            'salary' => 10000,
            'requirements' => ['PHP', 'Laravel', 'Vue.js'],
            'benefits' => ['Health insurance', 'Flexible hours'],
            'vacancies' => 2,
            'experience_level' => 'Senior',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->recruiterToken,
        ])->postJson('/api/job-listings', $jobData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'description', 'company_name', 'location', 
                    'type', 'salary', 'requirements', 'benefits'
                ],
            ]);

        $this->assertDatabaseHas('job_listings', [
            'title' => 'Senior Developer',
            'company_name' => 'Tech Corp',
            'user_id' => $this->recruiter->id,
        ]);
    }

    public function test_candidate_cannot_create_job_listing(): void
    {
        $jobData = [
            'title' => 'Senior Developer',
            'description' => 'We are looking for an experienced developer.',
            'company_name' => 'Tech Corp',
            'location' => 'Remote',
            'type' => 'CLT',
            'vacancies' => 2,
            'salary' => 10000
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->candidateToken,
        ])->postJson('/api/job-listings', $jobData);

        $response->assertStatus(403);
    }

    public function test_can_list_job_listings(): void
    {
        // Create some job listings
        JobListing::factory(5)->create([
            'user_id' => $this->recruiter->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->recruiterToken,
        ])->getJson('/api/job-listings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'company_name', 'location', 'type']
                ],
                'meta',
                'links',
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_can_show_job_listing(): void
    {
        $jobListing = JobListing::factory()->create([
            'user_id' => $this->recruiter->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->recruiterToken,
        ])->getJson('/api/job-listings/' . $jobListing->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'description', 'company_name', 'location', 
                    'type', 'salary', 'requirements', 'benefits'
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $jobListing->id,
                    'title' => $jobListing->title,
                ],
            ]);
    }

    public function test_recruiter_can_update_own_job_listing(): void
    {
        $jobListing = JobListing::factory()->create([
            'user_id' => $this->recruiter->id,
            'title' => 'Original Title',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->recruiterToken,
        ])->putJson('/api/job-listings/' . $jobListing->id, [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $jobListing->id,
                    'title' => 'Updated Title',
                ],
            ]);

        $this->assertDatabaseHas('job_listings', [
            'id' => $jobListing->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_recruiter_cannot_update_other_recruiter_job_listing(): void
    {
        $otherRecruiter = User::factory()->create(['role' => UserRole::RECRUITER]);
        $jobListing = JobListing::factory()->create([
            'user_id' => $otherRecruiter->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->recruiterToken,
        ])->putJson('/api/job-listings/' . $jobListing->id, [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_recruiter_can_delete_own_job_listing(): void
    {
        $jobListing = JobListing::factory()->create([
            'user_id' => $this->recruiter->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->recruiterToken,
        ])->deleteJson('/api/job-listings/' . $jobListing->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('job_listings', ['id' => $jobListing->id]);
    }
}
