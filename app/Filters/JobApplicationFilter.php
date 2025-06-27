<?php

namespace App\Filters;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;

class JobApplicationFilter extends ModelFilter
{
    /**
     * The filterable fields whitelist.
     * 
     * @var array
     */
    protected array $whitelist = [
        'jobListingId',
        'userId',
        'status',
        'createdAt',
        'activeJobsOnly',
        'search',
    ];
    
    public function jobListingId(int|string $jobListingId): Builder
    {
        return $this->builder->where('job_listing_id', $jobListingId);
    }

    public function userId(int|string $userId): Builder
    {
        return $this->builder->where('user_id', $userId);
    }
    
    public function status(string $status): Builder
    {
        $statusEnum = ApplicationStatus::tryFrom($status);
        if ($statusEnum) {
            return $this->builder->where('status', $statusEnum);
        }
        return $this->builder;
    }
    
    public function activeJobsOnly(mixed $activeJobsOnly): Builder
    {
        $isActive = filter_var($activeJobsOnly, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        
        if ($isActive === true) {
            return $this->builder->whereHas('jobListing', function (Builder $q) {
                $q->where('is_active', true);
            });
        }
        
        return $this->builder;
    }
    
    public function search(string $searchTerm): Builder
    {
        return $this->builder->where(function ($query) use ($searchTerm) {
            $query->where('cover_letter', 'like', "%{$searchTerm}%")
                  ->orWhere('additional_info', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%")
                  ->orWhereHas('jobListing', function ($q) use ($searchTerm) {
                      $q->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('company_name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('user', function ($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                  });
        });
    }
    
    public function createdAt(array|string $dateRange): Builder
    {
        if (is_array($dateRange)) {
            if (isset($dateRange['from'])) {
                $this->builder->whereDate('created_at', '>=', $dateRange['from']);
            }
            
            if (isset($dateRange['to'])) {
                $this->builder->whereDate('created_at', '<=', $dateRange['to']);
            }
        } else {
            $this->builder->whereDate('created_at', $dateRange);
        }
        
        return $this->builder;
    }
}
