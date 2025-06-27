<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;

class JobApplication extends Model
{
    use HasFactory, SoftDeletes, Filterable;
    
    protected $fillable = [
        'job_listing_id',
        'user_id',
        'cover_letter',
        'resume',
        'status',
        'additional_info',
        'notes'
    ];
    
    protected $casts = [
        'additional_info' => 'array',
        'status' => ApplicationStatus::class,
    ];
    
    /**
     * Relationships
     */
    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scopes
     */
    public function scopeForActiveJobs(Builder $query): Builder
    {
        return $query->whereHas('jobListing', function (Builder $q) {
            $q->where('is_active', true);
        });
    }
    
    public function scopeByStatus(Builder $query, ApplicationStatus $status): Builder
    {
        return $query->where('status', $status);
    }
    
    public function scopeForRecruiter(Builder $query, int $recruiterId): Builder
    {
        return $query->whereHas('jobListing', function (Builder $q) use ($recruiterId) {
            $q->where('user_id', $recruiterId);
        });
    }
    
    public function scopeForCandidate(Builder $query, int $candidateId): Builder
    {
        return $query->where('user_id', $candidateId);
    }
    
    /**
     * Business rules methods
     */
    public function canBeUpdatedByCandidate(): bool
    {
        return in_array($this->status, [
            ApplicationStatus::PENDING,
            ApplicationStatus::REVIEWED
        ]);
    }
    
    public function canBeWithdrawn(): bool
    {
        return !in_array($this->status, [
            ApplicationStatus::ACCEPTED,
            ApplicationStatus::REJECTED,
            ApplicationStatus::WITHDRAWN
        ]);
    }
    
    public function isActive(): bool
    {
        return $this->status !== ApplicationStatus::WITHDRAWN;
    }
    
    public function isFinal(): bool
    {
        return in_array($this->status, [
            ApplicationStatus::ACCEPTED,
            ApplicationStatus::REJECTED,
            ApplicationStatus::WITHDRAWN
        ]);
    }
}
