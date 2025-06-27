<?php

namespace Database\Factories;

use App\Enums\JobType;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobListing>
 */
class JobListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::where('role', UserRole::RECRUITER)->inRandomOrder()->first()->id ?? \App\Models\User::factory()->create(['role' => UserRole::RECRUITER])->id,
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(5, true),
            'company_name' => fake()->company(),
            'location' => fake()->city() . ', ' . fake()->country(),
            'type' => fake()->randomElement(JobType::cases()),
            'salary' => fake()->numberBetween(3000, 15000),
            'requirements' => $this->generateRequirements(),
            'benefits' => $this->generateBenefits(),
            'expiration_date' => fake()->dateTimeBetween('+1 week', '+3 months')->format('Y-m-d'),
            'is_active' => fake()->boolean(80),
            'vacancies' => fake()->numberBetween(1, 5),
            'experience_level' => fake()->randomElement(['Junior', 'Mid-level', 'Senior', 'Specialist']),
        ];
    }
    
    /**
     * Generate random job requirements
     */
    private function generateRequirements(): array
    {
        $requirements = [
            "Bachelor's degree in Computer Science or related field",
            'X+ years of experience in Y technology',
            'Strong knowledge of frontend development',
            'Strong knowledge of backend development',
            'Experience with RESTful APIs',
            'Experience with cloud platforms',
            'Team player with excellent communication skills',
            'Problem-solving skills',
            'Ability to work independently',
            'Fluent in English',
            'Experience with agile methodologies',
            'Knowledge of unit testing and automated testing',
            'Experience with version control systems, preferably Git',
            'Familiarity with CI/CD pipelines'
        ];
        
        return fake()->randomElements($requirements, fake()->numberBetween(4, 8));
    }
    
    /**
     * Generate random job benefits
     */
    private function generateBenefits(): array
    {
        $benefits = [
            'Competitive salary',
            'Remote work option',
            'Flexible hours',
            'Health insurance',
            'Dental insurance',
            'Vision insurance',
            'Life insurance',
            '401(k) matching',
            'Generous vacation policy',
            'Paid parental leave',
            'Professional development opportunities',
            'Gym membership',
            'Free snacks and drinks',
            'Company events',
            'Employee discounts',
            'Wellness program'
        ];
        
        return fake()->randomElements($benefits, fake()->numberBetween(3, 7));
    }
}
