<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        // Both recruiters and candidates can view applications
        return true;
    }

    public function view(User $user, JobApplication $jobApplication): bool
    {
        if ($user->isCandidate()) {
            // Candidates can only view their own applications
            return $jobApplication->user_id === $user->id;
        }
        
        if ($user->isRecruiter()) {
            // Recruiters can view applications for their job listings
            return $jobApplication->jobListing->user_id === $user->id;
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        // Only candidates can create applications
        return $user->role === UserRole::CANDIDATE;
    }

    public function update(User $user, JobApplication $jobApplication): bool
    {
        if ($user->isCandidate()) {
            // Candidates can update their own applications (with business rules in service)
            return $jobApplication->user_id === $user->id;
        }
        
        if ($user->isRecruiter()) {
            // Recruiters can update applications for their job listings
            return $user->role === UserRole::RECRUITER && $jobApplication->jobListing->user_id === $user->id;
        }
        
        return false;
    }

    public function withdraw(User $user, JobApplication $jobApplication): bool
    {
        // Only candidates can withdraw their own applications
        return $user->isCandidate() && $jobApplication->user_id === $user->id;
    }

    public function delete(User $user, JobApplication $jobApplication): bool
    {
        if ($user->isCandidate()) {
            // Candidates can only delete their own applications
            return $jobApplication->user_id === $user->id;
        }
        
        if ($user->isRecruiter()) {
            // Recruiters can delete applications for their job listings
            return $jobApplication->jobListing->user_id === $user->id;
        }
        
        return false;
    }

    public function bulkDelete(User $user, array $applicationIds): array
    {
        if ($user->isCandidate()) {
            // Candidates can only delete their own applications
            return JobApplication::whereIn('id', $applicationIds)
                ->where('user_id', $user->id)
                ->pluck('id')
                ->toArray();
        }
        
        if ($user->isRecruiter()) {
            // Recruiters can only delete applications for their job listings
            return JobApplication::whereIn('id', $applicationIds)
                ->whereHas('jobListing', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->pluck('id')
                ->toArray();
        }
        
        return [];
    }

    public function restore(User $user, JobApplication $jobApplication): bool
    {
        // Same rules as delete
        return $this->delete($user, $jobApplication);
    }

    public function forceDelete(User $user, JobApplication $jobApplication): bool
    {
        // Same rules as delete
        return $this->delete($user, $jobApplication);
    }
}
