<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'recruiter@example.com'],
            [
                'name' => 'Admin Recruiter',
                'password' => bcrypt('password'),
                'role' => UserRole::RECRUITER,
            ]
        );

        User::firstOrCreate(
            ['email' => 'candidate@example.com'],
            [
                'name' => 'Admin Candidate',
                'password' => bcrypt('password'),
                'role' => UserRole::CANDIDATE,
            ]
        );
        
        if (User::where('role', UserRole::RECRUITER)->count() < 10) {
            User::factory(8)->create(['role' => UserRole::RECRUITER]);
        }
        
        if (User::where('role', UserRole::CANDIDATE)->count() < 22) {
            User::factory(20)->create(['role' => UserRole::CANDIDATE]);
        }
        
        if (\App\Models\JobListing::count() < 30) {
            \App\Models\JobListing::factory(30)->create();
        }
        
        if (\App\Models\JobApplication::count() < 50) {
            \App\Models\JobApplication::factory(50)->create();
        }
    }
}
