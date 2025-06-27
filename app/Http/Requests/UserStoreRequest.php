<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Enums\UserRole;

class UserStoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'role' => 'required|in:' . implode(',', [
                UserRole::RECRUITER->value,
                UserRole::CANDIDATE->value
            ]),
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_image' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'experience' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }
}
