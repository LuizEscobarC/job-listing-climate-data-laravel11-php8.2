<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'bio' => $this->bio,
            'phone' => $this->phone,
            'address' => $this->address,
            'profile_image' => $this->profile_image,
            'skills' => $this->skills,
            'experience' => $this->experience,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'job_listings' => $this->when(
                $request->query('include_job_listings') && $this->role === UserRole::RECRUITER, 
                JobListingResource::collection($this->whenLoaded('jobListings'))
            ),
            'job_applications' => $this->when(
                $request->query('include_applications') && $this->role === UserRole::RECRUITER,
                JobApplicationResource::collection($this->whenLoaded('jobApplications'))
            ),
        ];
    }
}
