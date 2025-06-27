<?php

namespace App\Observers;

use App\Models\JobListing;
use Illuminate\Support\Facades\Cache;

class JobListingObserver
{
    public function created(JobListing $jobListing): void
    {
        $this->invalidateJobListingCache($jobListing);
    }

    public function updated(JobListing $jobListing): void
    {
        $this->invalidateJobListingCache($jobListing);
    }

    public function deleted(JobListing $jobListing): void
    {
        $this->invalidateJobListingCache($jobListing);
    }

    public function restored(JobListing $jobListing): void
    {
        $this->invalidateJobListingCache($jobListing);
    }

    public function forceDeleted(JobListing $jobListing): void
    {
        $this->invalidateJobListingCache($jobListing);
    }
    
    /**
     * Invalidação de cache com média granularidade
     */
    private function invalidateJobListingCache(JobListing $jobListing): void
    {
        $tags = [
            'job_listings',
            "job_listing:{$jobListing->id}",
        ];

        if ($jobListing->type) {
            $tags[] = "job_type:{$jobListing->type->value}";
        }
        
        if ($jobListing->is_active !== null) {
            $tags[] = $jobListing->is_active ? 'job_status:active' : 'job_status:inactive';
        }

        Cache::tags($tags)->flush();
        
        logger('Cache invalidated for JobListing via Observer', [
            'job_id' => $jobListing->id,
            'invalidated_tags' => $tags
        ]);
    }
}
