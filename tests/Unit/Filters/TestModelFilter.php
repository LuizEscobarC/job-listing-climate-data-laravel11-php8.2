<?php

namespace Tests\Unit\Filters;

use App\Filters\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class that extends the ModelFilter class for testing purposes.
 */
class TestModelFilter extends ModelFilter
{
    protected array $whitelist = ['name', 'email'];
    protected array $blacklist = ['password', 'token'];
    
    public function name($value)
    {
        return $this->builder->where('name', 'LIKE', "%{$value}%");
    }
    
    public function email($value)
    {
        return $this->builder->where('email', 'LIKE', "%{$value}%");
    }
    
    public function password($value)
    {
        return $this->builder->where('password', $value);
    }
    
    public function token($value)
    {
        return $this->builder->where('token', $value);
    }
    
    public function role($value)
    {
        return $this->builder->where('role', $value);
    }
}
