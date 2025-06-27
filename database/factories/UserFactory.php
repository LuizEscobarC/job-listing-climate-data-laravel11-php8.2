<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement([UserRole::CANDIDATE, UserRole::RECRUITER]),
            'bio' => fake()->paragraph(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'profile_image' => 'https://i.pravatar.cc/300?u=' . Str::random(8),
            'skills' => $this->generateSkills(),
            'experience' => $this->generateExperience(),
            'is_active' => true,
        ];
    }
    
    private function generateSkills(): array
    {
        $skills = [
            'PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'Angular', 
            'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Docker', 'AWS',
            'DevOps', 'Git', 'Node.js', 'Python', 'Java', 'C#', '.NET',
            'Ruby', 'Ruby on Rails', 'Go', 'Rust', 'Swift', 'Kotlin'
        ];
        
        return fake()->randomElements($skills, fake()->numberBetween(3, 8));
    }
    
    /**
     * Generate random work experience
     */
    private function generateExperience(): array
    {
        $experience = [];
        $count = fake()->numberBetween(1, 4);
        
        for ($i = 0; $i < $count; $i++) {
            $startDate = fake()->dateTimeBetween('-10 years', '-1 year');
            
            $experience[] = [
                'company' => fake()->company(),
                'position' => fake()->jobTitle(),
                'description' => fake()->paragraphs(2, true),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => fake()->boolean(70) 
                    ? fake()->dateTimeBetween($startDate, 'now')->format('Y-m-d') 
                    : null,
            ];
        }
        
        return $experience;
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
