<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobApplicationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Will check authorization in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_listing_id' => 'required|exists:job_listings,id',
            'cover_letter' => 'nullable|string',
            'resume' => 'nullable|string|max:255',
            'additional_info' => 'nullable|array',
        ];
    }
}
