<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobListingFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string'],
            'companyName' => ['sometimes', 'string'],
            'location' => ['sometimes', 'string'],
            'type' => ['sometimes', 'string'],
            'salaryMin' => ['sometimes', 'numeric', 'min:0'],
            'salaryMax' => ['sometimes', 'numeric', 'min:0', 'gte:salaryMin'],
            'experienceLevel' => ['sometimes', 'string'],
            'isActive' => ['sometimes', 'boolean'], // 1 or 0
            'expirationDate' => ['sometimes', 'array'],
            'expirationDate.from' => ['sometimes', 'date'],
            'expirationDate.to' => ['sometimes', 'date', 'after_or_equal:expirationDate.from'],
            'search' => ['sometimes', 'string'],
            'createdAt' => ['sometimes', 'array'],
            'createdAt.from' => ['sometimes', 'date'],
            'createdAt.to' => ['sometimes', 'date', 'after_or_equal:createdAt.from'],
            'include_user' => ['sometimes', 'boolean'],
            'include_applications' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'order_by' => ['sometimes', 'string', 'in:created_at,updated_at,title,salary,company_name'],
            'order_direction' => ['sometimes', 'string', 'in:asc,desc'],
            'skip_cache' => ['sometimes', 'boolean'],
        ];
    }
}
