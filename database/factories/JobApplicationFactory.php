<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jobListing = \App\Models\JobListing::inRandomOrder()->first() ?? \App\Models\JobListing::factory()->create();
        
        return [
            'job_listing_id' => $jobListing->id,
            'user_id' => \App\Models\User::where('role', UserRole::CANDIDATE)->inRandomOrder()->first()->id 
                ?? \App\Models\User::factory()->create(['role' => UserRole::CANDIDATE])->id,
            'cover_letter' => fake()->paragraphs(3, true),
            'resume' => 'resumes/' . fake()->uuid() . '.pdf',
            'status' => fake()->randomElement(ApplicationStatus::cases())->value,
            'additional_info' => $this->generateAdditionalInfo(),
            'notes' => fake()->boolean(70) ? fake()->paragraphs(2, true) : null,
        ];
    }
    
    /**
     * Generate random additional information
     */
    private function generateAdditionalInfo(): array
    {
        $info = [];
        
        if (fake()->boolean(70)) {
            $info['availability'] = fake()->randomElement(['Immediate', '2 weeks', '1 month', 'Negotiable']);
        }
        
        if (fake()->boolean(60)) {
            $info['desired_salary'] = fake()->numberBetween(3000, 12000);
        }
        
        if (fake()->boolean(50)) {
            $info['linkedin'] = 'https://linkedin.com/in/' . fake()->userName();
        }
        
        if (fake()->boolean(40)) {
            $info['portfolio'] = 'https://portfolio.' . fake()->domainName();
        }
        
        if (fake()->boolean(30)) {
            $info['github'] = 'https://github.com/' . fake()->userName();
        }
        
        return $info;
    }
}
