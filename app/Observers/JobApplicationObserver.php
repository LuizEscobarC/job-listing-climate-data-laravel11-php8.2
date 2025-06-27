<?php

namespace App\Observers;

use App\Models\JobApplication;
use Illuminate\Support\Facades\Cache;

class JobApplicationObserver
{
    public function created(JobApplication $jobApplication): void
    {
        $this->invalidateApplicationCaches($jobApplication);
    }

    public function updated(JobApplication $jobApplication): void
    {
        $this->invalidateApplicationCaches($jobApplication);
    }

    public function deleted(JobApplication $jobApplication): void
    {
        $this->invalidateApplicationCaches($jobApplication);
    }

    public function restored(JobApplication $jobApplication): void
    {
        $this->invalidateApplicationCaches($jobApplication);
    }

    public function forceDeleted(JobApplication $jobApplication): void
    {
        $this->invalidateApplicationCaches($jobApplication);
    }

    private function invalidateApplicationCaches(JobApplication $jobApplication): void
    {
        if (!$jobApplication->relationLoaded('jobListing')) {
            $jobApplication->load('jobListing');
        }
        
        if (!$jobApplication->relationLoaded('user')) {
            $jobApplication->load('user');
        }

        $cacheTags = [
            'job_applications',
            "job_application:{$jobApplication->id}",
            "user_applications:{$jobApplication->user_id}",
            "role_applications:{$jobApplication->user->role->value}",
            "job_listing_applications:{$jobApplication->job_listing_id}",
            "status_applications:{$jobApplication->status->value}",
            "recruiter_applications:{$jobApplication->jobListing->user_id}",
        ];

        foreach ($cacheTags as $tag) {
            Cache::tags($tag)->flush();
        }
    }
}
