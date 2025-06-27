<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Filterable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, Filterable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bio',
        'phone',
        'address',
        'profile_image',
        'skills',
        'experience',
        'is_active',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'skills' => 'array',
            'experience' => 'array',
            'is_active' => 'boolean',
            'role' => UserRole::class,
        ];
    }
    
    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }
    
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
    
    public function isRecruiter(): bool
    {
        return $this->role === UserRole::RECRUITER;
    }
    
    public function isCandidate(): bool
    {
        return $this->role === UserRole::CANDIDATE;
    }
}
