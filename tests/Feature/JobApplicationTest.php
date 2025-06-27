<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationTest extends TestCase
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
        
        // Create a job listing
        $this->jobListing = JobListing::factory()->create([
            'user_id' => $this->recruiter->id,
            'is_active' => true,
        ]);
    }

    public function test_candidate_can_apply_for_job(): void
    {
        $applicationData = [
            'job_listing_id' => $this->jobListing->id,
            'cover_letter' => 'I am very interested in this position.',
            'additional_info' => [
                'availability' => 'Immediate',
                'desired_salary' => 9000,
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->candidateToken,
        ])->postJson('/api/job-applications', $applicationData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'job_listing_id', 'user_id', 'cover_letter', 'status', 'additional_info'
                ],
            ]);

        $this->assertDatabaseHas('job_applications', [
            'job_listing_id' => $this->jobListing->id,
            'user_id' => $this->candidate->id,
        ]);
    }

    public function test_candidate_cannot_apply_twice_for_same_job(): void
    {
        // Create an existing application
        JobApplication::factory()->create([
            'job_listing_id' => $this->jobListing->id,
            'user_id' => $this->candidate->id,
        ]);

        $applicationData = [
            'job_listing_id' => $this->jobListing->id,
            'cover_letter' => 'I am very interested in this position.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->candidateToken,
        ])->postJson('/api/job-applications', $applicationData);

        $response->assertStatus(422);
    }

    public function test_recruiter_cannot_apply_for_job(): void
    {
        $applicationData = [
            'job_listing_id' => $this->jobListing->id,
            'cover_letter' => 'I am very interested in this position.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->recruiterToken,
        ])->postJson('/api/job-applications', $applicationData);

        $response->assertStatus(403);
    }

    public function test_candidate_can_list_own_applications(): void
    {
        // Create some applications
        JobApplication::factory(3)->create([
            'user_id' => $this->candidate->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->candidateToken,
        ])->getJson('/api/job-applications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'job_listing_id', 'user_id', 'status']
                ],
                'meta',
                'links',
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_recruiter_can_list_applications_for_own_job_listings(): void
    {
        // Create applications for the recruiter's job listing
        JobApplication::factory(3)->create([
            'job_listing_id' => $this->jobListing->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->recruiterToken,
        ])->getJson('/api/job-applications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'job_listing_id', 'user_id', 'status']
                ],
                'meta',
                'links',
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_recruiter_can_update_application_status(): void
    {
        $application = JobApplication::factory()->create([
            'job_listing_id' => $this->jobListing->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->recruiterToken,
        ])->putJson('/api/job-applications/' . $application->id, [
            'status' => 'interviewing',
            'notes' => 'Good candidate, schedule interview',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $application->id,
                    'status' => 'interviewing',
                ],
            ]);

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => 'interviewing',
            'notes' => 'Good candidate, schedule interview',
        ]);
    }

    public function test_candidate_can_update_own_application(): void
    {
        $application = JobApplication::factory()->create([
            'job_listing_id' => $this->jobListing->id,
            'user_id' => $this->candidate->id,
            'cover_letter' => 'Original cover letter',
            'status' => ApplicationStatus::PENDING->value,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->candidateToken,
        ])->putJson('/api/job-applications/' . $application->id, [
            'cover_letter' => 'Updated cover letter',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $application->id,
                    'cover_letter' => 'Updated cover letter',
                ],
            ]);

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'cover_letter' => 'Updated cover letter',
        ]);
    }

    public function test_candidate_can_withdraw_own_application(): void
    {
        $application = JobApplication::factory()->create([
            'job_listing_id' => $this->jobListing->id,
            'user_id' => $this->candidate->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->candidateToken,
        ])->patchJson('/api/job-applications/' . $application->id . '/withdraw');

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => __('messages.updated_successfully', ['resource' => __('messages.JobApplication')])]);
        
        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => 'withdrawn',
        ]);
    }

    public function test_candidate_cannot_withdraw_accepted_application(): void
    {
        $application = JobApplication::factory()->create([
            'job_listing_id' => $this->jobListing->id,
            'user_id' => $this->candidate->id,
            'status' => 'accepted',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->candidateToken,
        ])->patchJson('/api/job-applications/' . $application->id . '/withdraw');

        $response->assertStatus(422);
    }

    public function test_candidate_can_delete_own_application(): void
    {
        $application = JobApplication::factory()->create([
            'job_listing_id' => $this->jobListing->id,
            'user_id' => $this->candidate->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->candidateToken,
        ])->deleteJson('/api/job-applications/' . $application->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('job_applications', ['id' => $application->id]);
    }

    public function test_recruiter_can_delete_application_for_own_job_listing(): void
    {
        $application = JobApplication::factory()->create([
            'job_listing_id' => $this->jobListing->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->recruiterToken,
        ])->deleteJson('/api/job-applications/' . $application->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('job_applications', ['id' => $application->id]);
    }
}
