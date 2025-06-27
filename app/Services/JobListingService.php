<?php

namespace App\Services;

use App\Filters\JobListingFilter;
use App\Http\Requests\JobListingFilterRequest;
use App\Http\Resources\JobListingCollection;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class JobListingService
{

    public function getPaginatedJobListings(JobListingFilterRequest $request): JobListingCollection
    {
        $filters = $request->validated();
        $cacheKey = 'job_listings:' . md5(json_encode($filters));
        $cacheTTL = 60 * 15;
        
        $tags = ['job_listings', 'listings'];
        
        if (isset($filters['type'])) {
            $tags[] = "job_type:{$filters['type']}";
        }
        if (isset($filters['isactive'])) {
            $tags[] = $filters['isActive'] ? 'job_status:active' : 'job_status:inactive';
        }
        
        // Cache with tags to meddium granularity
        return Cache::tags($tags)->remember($cacheKey, $cacheTTL, function () use ($request) {
            $orderBy = $request->input('order_by', 'created_at');
            $orderDirection = $request->input('order_direction', 'desc');
            $perPage = $request->input('per_page', 15);
            
            $query = JobListing::filter(new JobListingFilter($request));
            
            if ($request->boolean('include_user')) {
                $query->with('user');
            }
            
            if ($request->boolean('include_applications')) {
                $query->with('applications');
            }
            
            $jobListings = $query->orderBy($orderBy, $orderDirection)->paginate($perPage);
            return new JobListingCollection($jobListings);
        });
    }

    public function getJobListingById(string $id, Request $request): JobListingResource
    {
        $cacheKey = "job_listing:{$id}:" . md5(serialize([
            'include_user' => $request->has('include_user'),
            'include_applications' => $request->has('include_applications'),
        ]));

        // Cache with tags to meddium granularity
        return Cache::tags(['job_listings', "job_listing:{$id}"])->remember($cacheKey, 60 * 30, function () use ($id, $request) {
            $query = JobListing::query();
            
            if ($request->has('include_user')) {
                $query->with('user');
            }
            
            if ($request->has('include_applications')) {
                $query->with('applications');
            }
            
            $jobListing = $query->find($id);
            if (!$jobListing) {
                throw new ModelNotFoundException(__('messages.job_listing_not_found'));
            }
            
            return new JobListingResource($jobListing);
        });
    }

    public function createJobListing(array $data): JobListingResource
    {
        $jobListing = JobListing::create($data);
        
        return new JobListingResource($jobListing);
    }

    public function updateJobListing(string $id, array $data): JobListingResource
    {
        $jobListing = JobListing::find($id);
        if (!$jobListing) {
            throw new ModelNotFoundException(__('messages.job_listing_not_found'));
        }
        $jobListing->update($data);

        return new JobListingResource($jobListing);
    }

    public function deleteJobListing(string $id): bool
    {
        $jobListing = JobListing::find($id);
        if (!$jobListing) {
            throw new ModelNotFoundException(__('messages.job_listing_not_found'));
        }
        
        $result = $jobListing->delete();
        
        return $result;
    }

    public function toggleJobListingStatus(string $id, bool $isActive): JobListingResource
    {
        $jobListing = JobListing::find($id);
        if (!$jobListing) {
            throw new ModelNotFoundException(__('messages.job_listing_not_found'));
        }
        
        $jobListing->update(['is_active' => $isActive]);
        
        return new JobListingResource($jobListing);
    }

    public function bulkDeleteJobListings(array $ids): int
    {
        $jobListings = JobListing::whereIn('id', $ids)->get();
        
        $deletedCount = 0;
        foreach ($jobListings as $jobListing) {
            if ($jobListing->delete()) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }

    public function bulkToggleJobListingsStatus(array $ids, bool $isActive): int
    {
        return JobListing::whereIn('id', $ids)->update(['is_active' => $isActive]);
    }
}
