<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use App\Filters\UserFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\Request;

class UserFilterTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => UserRole::CANDIDATE,
            'is_active' => true,
            'created_at' => now()->subDays(5)
        ]);

        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'role' => UserRole::RECRUITER,
            'is_active' => true,
            'created_at' => now()->subDays(3)
        ]);

        User::factory()->create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'role' => UserRole::CANDIDATE,
            'is_active' => false,
            'created_at' => now()->subDays(1)
        ]);
    }

    public function test_role_filter(): void
    {
        $request = Request::create('/', 'GET', ['role' => UserRole::CANDIDATE->value]);
        $filter = new UserFilter($request);
        $users = $filter->apply(User::query())->get();

        $this->assertCount(2, $users);
        $this->assertEquals('John Doe', $users[0]->name);
        $this->assertEquals('Bob Johnson', $users[1]->name);
    }

    public function test_is_active_filter(): void
    {
        $request = Request::create('/', 'GET', ['is_active' => true]);
        $filter = new UserFilter($request);
        $users = $filter->apply(User::query())->get();

        $this->assertCount(2, $users);
        $this->assertEquals('John Doe', $users[0]->name);
        $this->assertEquals('Jane Smith', $users[1]->name);
    }

    public function test_search_filter(): void
    {
        $request = Request::create('/', 'GET', ['search' => 'jane']);
        $filter = new UserFilter($request);
        $users = $filter->apply(User::query())->get();

        $this->assertCount(1, $users);
        $this->assertEquals('Jane Smith', $users[0]->name);
    }

    public function test_name_filter(): void
    {
        $request = Request::create('/', 'GET', ['name' => 'bob']);
        $filter = new UserFilter($request);
        $users = $filter->apply(User::query())->get();

        $this->assertCount(1, $users);
        $this->assertEquals('Bob Johnson', $users[0]->name);
    }

    public function test_email_filter(): void
    {
        $request = Request::create('/', 'GET', ['email' => 'john']);
        $filter = new UserFilter($request);
        $users = $filter->apply(User::query())->get();

        $this->assertCount(1, $users);
        $this->assertEquals('John Doe', $users[0]->name);
    }

    public function test_created_at_filter_single_date(): void
    {
        $date = now()->subDays(3)->format('Y-m-d');
        $request = Request::create('/', 'GET', ['created_at' => $date]);
        $filter = new UserFilter($request);
        $users = $filter->apply(User::query())->get();

        $this->assertCount(1, $users);
        $this->assertEquals('Jane Smith', $users[0]->name);
    }

    public function test_created_at_filter_range(): void
    {
        $from = now()->subDays(4)->format('Y-m-d');
        $to = now()->subDays(2)->format('Y-m-d');
        $request = Request::create('/', 'GET', ['created_at' => ['from' => $from, 'to' => $to]]);
        $filter = new UserFilter($request);
        $users = $filter->apply(User::query())->get();

        $this->assertCount(1, $users);
        $this->assertEquals('Jane Smith', $users[0]->name);
    }

    public function test_combined_filters(): void
    {
        $request = Request::create('/', 'GET', [
            'role' => UserRole::CANDIDATE->value,
            'is_active' => true
        ]);
        
        $filter = new UserFilter($request);
        $users = $filter->apply(User::query())->get();

        $this->assertCount(1, $users);
        $this->assertEquals('John Doe', $users[0]->name);
    }
}
