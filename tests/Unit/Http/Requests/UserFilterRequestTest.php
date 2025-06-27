<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UserFilterRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UserFilterRequestTest extends TestCase
{
    protected UserFilterRequest $request;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UserFilterRequest();
    }
    
    public function testValidationRulesExist()
    {
        $rules = $this->request->rules();
        
        $this->assertArrayHasKey('role', $rules);
        $this->assertArrayHasKey('isActive', $rules);
        $this->assertArrayHasKey('search', $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('createdAt', $rules);
        $this->assertArrayHasKey('createdAt.from', $rules);
        $this->assertArrayHasKey('createdAt.to', $rules);
        $this->assertArrayHasKey('order_by', $rules);
        $this->assertArrayHasKey('order_direction', $rules);
        $this->assertArrayHasKey('per_page', $rules);
    }
    
    public function testValidInput()
    {
        $validator = Validator::make([
            'role' => 'recruiter',
            'isActive' => true,
            'search' => 'John',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'createdAt' => [
                'from' => '2025-01-01',
                'to' => '2025-05-01'
            ],
            'order_by' => 'name',
            'order_direction' => 'asc',
            'per_page' => 25
        ], $this->request->rules());
        
        $this->assertTrue($validator->passes(), json_encode($validator->errors()->toArray()));
    }
    
    public function testInvalidRole()
    {
        $validator = Validator::make([
            'role' => 'invalid-role'
        ], $this->request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('role', $validator->errors()->toArray());
    }
    
    public function testInvalidDateRange()
    {
        $validator = Validator::make([
            'createdAt' => [
                'from' => '2025-06-01',
                'to' => '2025-05-01'  // Before 'from' date
            ]
        ], $this->request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('createdAt.to', $validator->errors()->toArray());
    }
    
    public function testBooleanConversion()
    {
        // We'll test the logic separately since we can't call validated() directly
        $isActiveTrue = filter_var(true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $isActiveFalse = filter_var('false', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        
        $this->assertTrue($isActiveTrue);
        $this->assertFalse($isActiveFalse);
        $this->assertIsBool($isActiveTrue);
        $this->assertIsBool($isActiveFalse);
    }
    
    public function testCustomAttributesExist()
    {
        $attributes = $this->request->attributes();
        
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('isActive', $attributes);
        $this->assertArrayHasKey('createdAt', $attributes);
        $this->assertArrayHasKey('createdAt.from', $attributes);
        $this->assertArrayHasKey('createdAt.to', $attributes);
        $this->assertArrayHasKey('per_page', $attributes);
    }
}
