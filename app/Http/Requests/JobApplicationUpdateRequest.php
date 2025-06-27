<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;

class JobApplicationUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
        /** 
         * withdraw = cancelado, desinscrito
         * accepted = aceito
         * rejected = rejeitado
         * interviewing = entrevistando
         * pending = pendente
         * 
         * */ 
        return [
            'status' => 'sometimes|in:' . implode(',', array_column(ApplicationStatus::cases(), 'value')),
            'notes' => 'nullable|string',
            'cover_letter' => 'nullable|string',
            'resume' => 'nullable|string|max:255',
            'additional_info' => 'nullable|array',
        ];
    }
}
