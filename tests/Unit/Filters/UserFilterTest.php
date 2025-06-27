<?php

namespace Tests\Unit\Filters;

use Tests\TestCase;
use App\Filters\UserFilter;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use ReflectionClass;

class UserFilterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testUserFilterHasRequiredWhitelist()
    {
        $filter = new UserFilter();
        
        $reflection = new ReflectionClass(UserFilter::class);
        $property = $reflection->getProperty('whitelist');
        $property->setAccessible(true);
        $whitelist = $property->getValue($filter);
        
        $this->assertIsArray($whitelist);
        $this->assertContains('role', $whitelist);
        $this->assertContains('isActive', $whitelist);
        $this->assertContains('search', $whitelist);
        $this->assertContains('name', $whitelist);
        $this->assertContains('email', $whitelist);
        $this->assertContains('createdAt', $whitelist);
    }

    public function testFilterByRole()
    {
        $request = new Request(['role' => 'recruiter']);
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')
            ->with('role', 'recruiter')
            ->once()
            ->andReturnSelf();
        
        $filter = new UserFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }

    public function testFilterByIsActive()
    {
        $request = new Request(['is_active' => true]);
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')
            ->with('is_active', true)
            ->once()
            ->andReturnSelf();
        
        $filter = new UserFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }

    public function testFilterBySearch()
    {
        $request = new Request(['search' => 'john']);
        $builder = Mockery::mock(Builder::class);
        $queryBuilder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')
            ->with(Mockery::type('Closure'))
            ->once()
            ->andReturnUsing(function($callback) use ($builder, $queryBuilder) {
                $callback($queryBuilder);
                return $builder;
            });
            
        $queryBuilder->shouldReceive('where')
            ->with('name', 'like', '%john%')
            ->once()
            ->andReturnSelf();
            
        $queryBuilder->shouldReceive('orWhere')
            ->with('email', 'like', '%john%')
            ->once()
            ->andReturnSelf();
        
        $filter = new UserFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }

    public function testFilterByName()
    {
        $request = new Request(['name' => 'john']);
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')
            ->with('name', 'LIKE', '%john%')
            ->once()
            ->andReturnSelf();
        
        $filter = new UserFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }

    public function testFilterByEmail()
    {
        $request = new Request(['email' => 'john@example.com']);
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')
            ->with('email', 'LIKE', '%john@example.com%')
            ->once()
            ->andReturnSelf();
        
        $filter = new UserFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }

    public function testFilterByDateRange()
    {
        $dateRange = [
            'from' => '2025-01-01',
            'to' => '2025-12-31'
        ];
        $request = new Request(['created_at' => $dateRange]);
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('whereDate')
            ->with('created_at', '>=', '2025-01-01')
            ->once()
            ->andReturnSelf();
            
        $builder->shouldReceive('whereDate')
            ->with('created_at', '<=', '2025-12-31')
            ->once()
            ->andReturnSelf();
        
        $filter = new UserFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }

    public function testFilterBySpecificDate()
    {
        $request = new Request(['created_at' => '2025-05-23']);
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('whereDate')
            ->with('created_at', '2025-05-23')
            ->once()
            ->andReturnSelf();
        
        $filter = new UserFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }

    public function testMultipleFiltersAreCombined()
    {
        $request = new Request([
            'role' => 'recruiter',
            'is_active' => true,
            'name' => 'john'
        ]);
        
        $builder = Mockery::mock(Builder::class);
        
        $builder->shouldReceive('where')
            ->with('role', 'recruiter')
            ->once()
            ->andReturnSelf();
            
        $builder->shouldReceive('where')
            ->with('is_active', true)
            ->once()
            ->andReturnSelf();
            
        $builder->shouldReceive('where')
            ->with('name', 'LIKE', '%john%')
            ->once()
            ->andReturnSelf();
        
        $filter = new UserFilter($request);
        $result = $filter->apply($builder);

        $this->assertSame($builder, $result);
    }
    
    public function testEmptyValuesAreIgnored()
    {
        $request = new Request(['role' => '', 'name' => null]);
        $builder = Mockery::mock(Builder::class);
        
        $filter = new UserFilter($request);
        $result = $filter->apply($builder);
        
        $this->assertSame($builder, $result);
    }
}