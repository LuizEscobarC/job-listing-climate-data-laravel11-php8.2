<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobApplicationFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jobListingId' => ['sometimes', 'integer', 'exists:job_listings,id'],
            'userId' => ['sometimes', 'integer', 'exists:users,id'],
            'status' => ['sometimes', 'string', Rule::enum(ApplicationStatus::class)],
            'activeJobsOnly' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'string'],
            'createdAt' => ['sometimes', 'array'],
            'createdAt.from' => ['sometimes', 'date'],
            'createdAt.to' => ['sometimes', 'date', 'after_or_equal:createdAt.from'],
            'include_job_listing' => ['sometimes', 'boolean'],
            'include_user' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'order_by' => ['sometimes', 'string', 'in:created_at,updated_at,status'],
            'order_direction' => ['sometimes', 'string', 'in:asc,desc'],
            'skip_cache' => ['sometimes', 'boolean'],
        ];
    }
}
