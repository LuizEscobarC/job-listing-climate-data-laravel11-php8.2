<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends ModelFilter
{
    /**
     * The filterable fields.
     *
     * @var array
     */
    protected array $whitelist = [
        'role',
        'isActive',
        'search',
        'name',
        'email',
        'createdAt',
    ];
    
    public function role(string $role): Builder
    {
        return $this->builder->where('role', $role);
    }

    public function isActive(mixed $isActive): Builder
    {
        $isActive = filter_var($isActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        
        if ($isActive !== null) {
            return $this->builder->where('is_active', $isActive);
        }
        
        return $this->builder;
    }

    public function search(string $searchTerm): Builder
    {
        return $this->builder->where(function ($query) use ($searchTerm) {
            $query->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
        });
    }

    public function name(string $name): Builder
    {
        return $this->whereLike('name', $name);
    }

    public function email(string $email): Builder
    {
        return $this->whereLike('email', $email);
    }

    /**
     * Summary of createdAt
     * @param mixed $dateRange
     * @return Builder
     */
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
