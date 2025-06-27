<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Enums\UserRole;

class UserUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // TODO: do the user must be update the password in that endpoint?
        // if the user is not updating the password, we don't need to validate it
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $this->user,
            'password' => 'sometimes|string|min:8|confirmed',
            'role' => 'sometimes|in:' . implode(',', [
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
