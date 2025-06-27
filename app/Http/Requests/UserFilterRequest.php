<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['sometimes', Rule::in(['recruiter', 'candidate'])],
            'isActive' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'string', 'max:100'],
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'string', 'max:100'],
            'createdAt' => ['sometimes', 'array'],
            'createdAt.from' => ['sometimes', 'nullable', 'date', 'before_or_equal:now'],
            'createdAt.to' => ['sometimes', 'nullable', 'date', 'after_or_equal:createdAt.from', 'before_or_equal:now'],
            'order_by' => ['sometimes', 'string', Rule::in(['created_at', 'name', 'email', 'role', 'is_active'])],
            'order_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        
        if (isset($validated['isActive']) && is_string($validated['isActive'])) {
            $validated['isActive'] = filter_var(
                $validated['isActive'], 
                FILTER_VALIDATE_BOOLEAN, 
                FILTER_NULL_ON_FAILURE
            );
        }

        return $validated;
    }

    public function attributes(): array
    {
        return [
            'isActive' => 'active status',
            'createdAt' => 'creation date',
            'createdAt.from' => 'start date',
            'createdAt.to' => 'end date',
            'per_page' => 'items per page',
        ];
    }
}
