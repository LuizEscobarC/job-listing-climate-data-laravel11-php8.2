<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\UserService;
use App\Models\User;
use App\Filters\UserFilter;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected UserService $userService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function testGetPaginatedUsersAppliesFilters()
    {
        $recruiter1 = User::factory()->create([
            'role' => UserRole::RECRUITER,
            'name' => 'John Recruiter',
            'email' => 'john@example.com',
            'is_active' => true
        ]);
        
        $recruiter2 = User::factory()->create([
            'role' => UserRole::RECRUITER,
            'name' => 'Jane Recruiter',
            'email' => 'jane@example.com',
            'is_active' => false
        ]);
        
        $candidate = User::factory()->create([
            'role' => UserRole::CANDIDATE,
            'name' => 'Bob Candidate',
            'email' => 'bob@example.com',
            'is_active' => true
        ]);
        
        $request = new Request(['role' => 'recruiter']);
        $result = $this->userService->getPaginatedUsers($request);
        
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(2, $result->total());
        $this->assertTrue($result->contains('id', $recruiter1->id));
        $this->assertTrue($result->contains('id', $recruiter2->id));
        $this->assertFalse($result->contains('id', $candidate->id));
        
        $request = new Request(['isActive' => true]);
        $result = $this->userService->getPaginatedUsers($request);
        
        $this->assertEquals(2, $result->total());
        $this->assertTrue($result->contains('id', $recruiter1->id));
        $this->assertFalse($result->contains('id', $recruiter2->id));
        $this->assertTrue($result->contains('id', $candidate->id));
        
        $request = new Request(['name' => 'John']);
        $result = $this->userService->getPaginatedUsers($request);
        
        $this->assertEquals(1, $result->total());
        $this->assertTrue($result->contains('id', $recruiter1->id));
        
        $request = new Request(['search' => 'john']);
        $result = $this->userService->getPaginatedUsers($request);
        
        $this->assertEquals(1, $result->total());
        $this->assertTrue($result->contains('id', $recruiter1->id));
        
        $request = new Request([
            'role' => 'recruiter',
            'isActive' => true
        ]);
        $result = $this->userService->getPaginatedUsers($request);
        
        $this->assertEquals(1, $result->total());
        $this->assertTrue($result->contains('id', $recruiter1->id));
        $this->assertFalse($result->contains('id', $recruiter2->id));
        $this->assertFalse($result->contains('id', $candidate->id));
    }

    public function testFindUserById()
    {
        $user = User::factory()->create();
        
        $result = $this->userService->findUserById($user->id);
        
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->name, $result->name);
        $this->assertEquals($user->email, $result->email);
    }
    
    public function testCreateUser()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => UserRole::RECRUITER,
            'is_active' => true
        ];
        
        $user = $this->userService->createUser($userData);
        
        $this->assertEquals('New User', $user->name);
        $this->assertEquals('newuser@example.com', $user->email);
        $this->assertEquals(UserRole::RECRUITER, $user->role);
        $this->assertTrue($user->is_active);
        
        $this->assertNotEquals('password123', $user->password);
    }
    
    public function testUpdateUser()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);
        
        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];
        
        $updatedUser = $this->userService->updateUser($user, $updatedData);
        
        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals('updated@example.com', $updatedUser->email);
    }
    
    public function testDeleteAndRestoreUser()
    {
        $user = User::factory()->create();
        
        $result = $this->userService->deleteUser($user);
        $this->assertTrue($result);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        
        $restoredUser = $this->userService->restoreUser($user->id);
        $this->assertInstanceOf(User::class, $restoredUser);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }
    
    public function testSetUserActiveStatus()
    {
        $user = User::factory()->create(['is_active' => true]);
        
        $result = $this->userService->setUserActiveStatus($user, false);
        $this->assertFalse($result->is_active);
        
        $result = $this->userService->setUserActiveStatus($user, true);
        $this->assertTrue($result->is_active);
    }
}
