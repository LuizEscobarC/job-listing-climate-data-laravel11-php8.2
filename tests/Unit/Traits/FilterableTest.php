<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use App\Traits\Filterable;
use App\Filters\UserFilter;
use Illuminate\Database\Eloquent\Builder;
use Mockery;

class FilterableTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Cria uma classe anônima que usa o trait Filterable
     */
    private function getFilterableClass()
    {
        return new class {
            use Filterable;
            
            public function newQuery()
            {
                return Mockery::mock(Builder::class);
            }
            
            public function getTable()
            {
                return 'test_table';
            }
        };
    }
    
    public function testFilterScopeAppliesCorrectFilter()
    {
        $model = $this->getFilterableClass();
        $filter = Mockery::mock(UserFilter::class);
        $builder = Mockery::mock(Builder::class);
        $returnedBuilder = Mockery::mock(Builder::class);
        
        $filter->shouldReceive('apply')
            ->once()
            ->with($builder)
            ->andReturn($returnedBuilder);
        
        $result = $model->scopeFilter($builder, $filter);
        
        $this->assertSame($returnedBuilder, $result);
    }
}
