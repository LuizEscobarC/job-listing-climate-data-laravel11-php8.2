<?php

namespace App\Traits;

use App\Filters\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Trait for making Eloquent models filterable.
 * 
 * This trait allows eloquent models to use filters through a fluent API.
 * It automatically finds the corresponding filter class for the model
 * or uses a provided filter instance.
 */
trait Filterable
{
    public function scopeFilter(Builder $query, ModelFilter $filter = null): Builder
    {
        if ($filter === null) {
            $filter = $this->getModelFilterClass();
        }
        
        if ($filter instanceof ModelFilter) {
            return $filter->apply($query);
        }
        
        return $query;
    }

    public function getModelFilterClass(): ModelFilter
    {
        $modelClass = get_class($this);
        $filterClass = str_replace('\\Models\\', '\\Filters\\', $modelClass) . 'Filter';
        
        if (class_exists($filterClass)) {
            return new $filterClass(request());
        }
        
        return new \App\Filters\UserFilter(request());
    }
}
