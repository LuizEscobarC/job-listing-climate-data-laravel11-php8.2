<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

trait FeatureTestTrait
{
    use LazilyRefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
    }
}
