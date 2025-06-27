<?php

namespace Tests\Unit\Filters;

use Tests\TestCase;
use App\Filters\ModelFilter;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Mockery;

class ModelFilterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function testItAppliesWhitelistedFilters()
    {
        $request = new Request(['name' => 'John', 'email' => 'john@example.com']);
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')
            ->with('name', 'LIKE', '%John%')
            ->once()
            ->andReturnSelf();
            
        $builder->shouldReceive('where')
            ->with('email', 'LIKE', '%john@example.com%')
            ->once()
            ->andReturnSelf();
        
        $filter = new TestModelFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }
    
    public function testItIgnoresBlacklistedFilters()
    {
        $request = new Request([
            'name' => 'John',
            'password' => 'secret',
            'token' => '12345'
        ]);
        
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')
            ->with('name', 'LIKE', '%John%')
            ->once()
            ->andReturnSelf();
            
        $builder->shouldNotReceive('where')
            ->with('password', 'secret');
            
        $builder->shouldNotReceive('where')
            ->with('token', '12345');
        
        $filter = new TestModelFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }
    
    public function testItIgnoresNonWhitelistedFiltersWhenWhitelistIsUsed()
    {
        $request = new Request([
            'name' => 'John',
            'role' => 'admin' // Not in whitelist
        ]);
        
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')
            ->with('name', 'LIKE', '%John%')
            ->once()
            ->andReturnSelf();
            
        $builder->shouldNotReceive('where')
            ->with('role', 'admin');
        
        $filter = new TestModelFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }
    
    public function testItIgnoresNullOrEmptyValues()
    {
        $request = new Request([
            'name' => '',
            'email' => null
        ]);
        
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldNotReceive('where');
        
        $filter = new TestModelFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }
    
    public function testItHandlesArrayInputInsteadOfRequest()
    {
        $inputArray = ['name' => 'John', 'email' => 'john@example.com'];
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')
            ->with('name', 'LIKE', '%John%')
            ->once()
            ->andReturnSelf();
            
        $builder->shouldReceive('where')
            ->with('email', 'LIKE', '%john@example.com%')
            ->once()
            ->andReturnSelf();
        
        $filter = new TestModelFilter($inputArray);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }
    
    public function testItProvidesHelperMethodsForCommonQueries()
    {
        $request = new Request();
        $builder = Mockery::mock(Builder::class);
        $filter = new class($request) extends ModelFilter {
            public function testWhereLike($value)
            {
                return $this->whereLike('test_column', $value);
            }
            
            public function testWhereIn($value)
            {
                return $this->whereIn('test_column', $value);
            }
        };
        
        $builder->shouldReceive('where')
            ->with('test_column', 'LIKE', '%test%')
            ->once()
            ->andReturnSelf();
            
        $builder->shouldReceive('whereIn')
            ->with('test_column', [1, 2, 3])
            ->once()
            ->andReturnSelf();
            
        $result = $filter->apply($builder);
        $this->assertSame($builder, $result);
        
        $this->assertSame($builder, $filter->testWhereLike('test'));
        $this->assertSame($builder, $filter->testWhereIn([1, 2, 3]));
    }
}