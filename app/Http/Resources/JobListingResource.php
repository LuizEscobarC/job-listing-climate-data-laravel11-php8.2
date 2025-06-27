<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'company_name' => $this->company_name,
            'location' => $this->location,
            'type' => $this->type,
            'salary' => $this->salary,
            'requirements' => $this->requirements,
            'benefits' => $this->benefits,
            'expiration_date' => $this->expiration_date,
            'is_active' => $this->is_active,
            'vacancies' => $this->vacancies,
            'experience_level' => $this->experience_level,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'recruiter' => new UserResource($this->whenLoaded('user')),
            'applications_count' => $this->when($request->query('include_counts'), $this->applications()->count()),
            'applications' => $this->when(
                $request->query('include_applications'), 
                JobApplicationResource::collection($this->whenLoaded('applications'))
            ),
        ];
    }
}
