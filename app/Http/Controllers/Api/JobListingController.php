<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobListingFilterRequest;
use App\Http\Requests\JobListingStoreRequest;
use App\Http\Requests\JobListingUpdateRequest;
use App\Models\JobListing;
use App\Services\JobListingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JobListingController extends Controller
{
    use AuthorizesRequests;
    
    private JobListingService $jobListingService;

    public function __construct(JobListingService $jobListingService)
    {
        $this->jobListingService = $jobListingService;
    }

    /**
     * @param JobListingFilterRequest $request
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(JobListingFilterRequest $request)
    {
        return $this->jobListingService->getPaginatedJobListings($request);
    }

    /**
     * @param JobListingStoreRequest $request
     * @return \App\Http\Resources\JobListingResource
     */
    public function store(JobListingStoreRequest $request)
    {
        $this->authorize('create', JobListing::class);
        
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        
        return $this->jobListingService->createJobListing($data);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return \App\Http\Resources\JobListingResource
     */
    public function show(string $id, Request $request)
    {
        return $this->jobListingService->getJobListingById($id, $request);
    }

    /**
     * @param JobListingUpdateRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse|\App\Http\Resources\JobListingResource
     */
    public function update(JobListingUpdateRequest $request, string $id)
    {
        $jobListing = JobListing::findOrFail($id);
        $this->authorize('update', $jobListing);
        
        $data = $request->validated();
        return $this->jobListingService->updateJobListing($id, $data);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, string $id)
    {
        $jobListing = JobListing::findOrFail($id);
        $this->authorize('delete', $jobListing);
        
        $this->jobListingService->deleteJobListing($id);
        
        return response()->json([
            'message' => __('messages.deleted_successfully', ['resource' => __('messages.JobListing')])
        ], 200);
    }

    /**
     * Toggle job listing status (activate/deactivate)
     * 
     * @param Request $request
     * @param string $id
     * @return \App\Http\Resources\JobListingResource
     */
    public function toggleStatus(Request $request, string $id)
    {
        $jobListing = JobListing::find($id);
        if (!$jobListing) {
           throw new ModelNotFoundException("Vaga de emprego [$id] não encontrada.");
        }
        $this->authorize('toggleStatus', $jobListing);
        
        $isActive = $request->boolean('is_active');
        
        return $this->jobListingService->toggleJobListingStatus($id, $isActive);
    }

    /**
     * Bulk delete multiple job listings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string|exists:job_listings,id'
        ]);
        
        // Check authorization for each job listing
        $jobListings = JobListing::whereIn('id', $request->input('ids'))->get();
        foreach ($jobListings as $jobListing) {
            $this->authorize('delete', $jobListing);
        }
        
        $deletedCount = $this->jobListingService->bulkDeleteJobListings($request->input('ids'));
        
        return response()->json([
            'message' => __('messages.bulk_deleted_successfully', [
                'count' => $deletedCount,
                'resource' => __('messages.job_listings')
            ]),
            'deleted_count' => $deletedCount
        ], 200);
    }

    /**
     * Bulk toggle status for multiple job listings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkToggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string|exists:job_listings,id',
            'is_active' => 'required|boolean'
        ]);
        
        // Check authorization for each job listing
        $jobListings = JobListing::whereIn('id', $request->input('ids'))->get();
        foreach ($jobListings as $jobListing) {
            $this->authorize('toggleStatus', $jobListing);
        }
        
        $updatedCount = $this->jobListingService->bulkToggleJobListingsStatus(
            $request->input('ids'),
            $request->boolean('is_active')
        );
        
        return response()->json([
            'message' => __('messages.bulk_status_updated_successfully', [
                'count' => $updatedCount,
                'resource' => __('messages.job_listings')
            ]),
            'updated_count' => $updatedCount
        ], 200);
    }
}
