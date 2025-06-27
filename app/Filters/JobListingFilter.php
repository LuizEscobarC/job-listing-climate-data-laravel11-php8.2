<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class JobListingFilter extends ModelFilter
{
    /**
     * The filterable fields whitelist.
     * 
     * @var array
     */
    protected array $whitelist = [
        'title',
        'companyName',
        'location',
        'type',
        'salaryMin',
        'salaryMax',
        'experienceLevel',
        'isActive',
        'expirationDate',
        'search',
        'createdAt',
    ];
    
    public function title(string $title): Builder
    {
        return $this->whereLike('title', $title);
    }

    public function companyName(string $companyName): Builder
    {
        return $this->whereLike('company_name', $companyName);
    }
    
    public function location(string $location): Builder
    {
        return $this->whereLike('location', $location);
    }
    
    public function type(string $type): Builder
    {
        return $this->builder->where('type', $type);
    }

    public function salaryMin(int|string $minSalary): Builder
    {
        return $this->builder->where('salary', '>=', $minSalary);
    }
    
    public function salaryMax(int|string $maxSalary): Builder
    {
        return $this->builder->where('salary', '<=', $maxSalary);
    }
    
    public function experienceLevel(string $level): Builder
    {
        return $this->builder->where('experience_level', $level);
    }
    
    public function isActive(mixed $isActive): Builder
    {
        $isActive = filter_var($isActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        
        if ($isActive !== null) {
            return $this->builder->where('is_active', $isActive);
        }
        
        return $this->builder;
    }
    
    public function expirationDate(array|string $dateRange): Builder
    {
        if (is_array($dateRange)) {
            if (isset($dateRange['from'])) {
                $this->builder->whereDate('expiration_date', '>=', $dateRange['from']);
            }
            
            if (isset($dateRange['to'])) {
                $this->builder->whereDate('expiration_date', '<=', $dateRange['to']);
            }
        } else {
            $this->builder->whereDate('expiration_date', $dateRange);
        }
        
        return $this->builder;
    }
    
    public function search(string $searchTerm): Builder
    {
        return $this->builder->where(function ($query) use ($searchTerm) {
            $query->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('company_name', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%");
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
