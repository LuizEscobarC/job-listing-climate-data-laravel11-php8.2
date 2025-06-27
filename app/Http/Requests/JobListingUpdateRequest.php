<?php

namespace App\Http\Requests;

use App\Enums\JobType;
use Illuminate\Foundation\Http\FormRequest;

class JobListingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'company_name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:' . implode(',', array_column(JobType::cases(), 'value')),
            'salary' => 'nullable|numeric',
            'requirements' => 'nullable|array',
            'benefits' => 'nullable|array',
            'expiration_date' => 'nullable|date',
            'is_active' => 'boolean',
            'vacancies' => 'sometimes|integer|min:1',
            'experience_level' => 'nullable|string|max:100',
        ];
    }
}
