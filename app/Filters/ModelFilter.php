<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Base class for Eloquent model filters
 *
 * This implementation is inspired by the tucker-eric/eloquentfilter package
 * and uses the Strategy and Chain of Responsibility design patterns
 * in a more concise and straightforward way.
 */
abstract class ModelFilter
{
    protected Request $request;

    protected Builder $builder;
    
    protected array $blacklist = [];

    protected array $whitelist = [];

    public function __construct(Request|array $request = null)
    {
        if ($request instanceof Request) {
            $this->request = $request;
        } elseif (is_array($request)) {
            $this->request = new Request($request);
        } else {
            $this->request = app('request');
        }
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;
        
        foreach ($this->getFilters() as $filter => $value) {
            $methodName = Str::camel($filter);
            
            if (($value === '' || $value === null) || $this->isBlacklisted($methodName)) {
                continue;
            }
            
            if (!empty($this->whitelist) && !$this->isWhitelisted($methodName)) {
                continue;
            }
            
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }
        
        return $this->builder;
    }

    protected function getFilters(): array
    {
        return $this->request->all();
    }

    protected function isBlacklisted(string $methodName): bool
    {
        return in_array($methodName, $this->blacklist);
    }

    protected function isWhitelisted(string $methodName): bool
    {
        return in_array($methodName, $this->whitelist);
    }

    protected function whereLike(string $column, string $value): Builder
    {
        return $this->builder->where($column, 'LIKE', "%{$value}%");
    }

    protected function whereIn(string $column, array $values): Builder
    {
        return $this->builder->whereIn($column, $values);
    }

    protected function whereDate(string $column, string $operator, string $value): Builder
    {
        return $this->builder->whereDate($column, $operator, $value);
    }

    protected function whereBetween(string $column, array $values): Builder
    {
        return $this->builder->whereBetween($column, $values);
    }

    protected function whereNull(string $column): Builder
    {
        return $this->builder->whereNull($column);
    }

    protected function whereNotNull(string $column): Builder
    {
        return $this->builder->whereNotNull($column);
    }

    public function __call($method, $args): mixed
    {
        if (method_exists($this->builder, $method)) {
            return call_user_func_array([$this->builder, $method], $args);
        }
        
        return $this->builder;
    }
}
