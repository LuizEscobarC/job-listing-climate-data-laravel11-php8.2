<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JobListingPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // anywhere can view
    }

    public function view(User $user, JobListing $jobListing): bool
    {
        return true; // anywhere
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::RECRUITER; // just the recruiter can create
    }

    public function update(User $user, JobListing $jobListing): bool
    {
        return $user->role === UserRole::RECRUITER && $user->id === $jobListing->user_id;
    }

    public function delete(User $user, JobListing $jobListing): bool
    {
        return $user->role === UserRole::RECRUITER && $user->id === $jobListing->user_id;
    }

    public function toggleStatus(User $user, JobListing $jobListing): bool
    {
        return $user->role === UserRole::RECRUITER && $user->id === $jobListing->user_id;
    }

    public function restore(User $user, JobListing $jobListing): bool
    {
        return $user->role === UserRole::RECRUITER;
    }

    public function forceDelete(User $user, JobListing $jobListing): bool
    {
        return $user->role === UserRole::RECRUITER && $user->id === $jobListing->user_id;
    }
}
