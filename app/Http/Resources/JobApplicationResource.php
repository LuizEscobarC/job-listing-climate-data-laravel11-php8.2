<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_listing_id' => $this->job_listing_id,
            'user_id' => $this->user_id,
            'cover_letter' => $this->cover_letter,
            'resume' => $this->resume,
            'status' => $this->status,
            'additional_info' => $this->additional_info,
            'notes' => $this->when($request->user() && $request->user()->isRecruiter(), $this->notes),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'job_listing' => new JobListingResource($this->whenLoaded('jobListing')),
            'candidate' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
