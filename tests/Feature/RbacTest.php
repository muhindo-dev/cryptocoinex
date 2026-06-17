<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
    }

    public function test_roles_sync_from_legacy_column(): void
    {
        $admin = User::create(['name' => 'Ad', 'email' => 'ad@a.com', 'password' => bcrypt('x'), 'role' => 'admin']);
        $officer = User::create(['name' => 'Of', 'email' => 'of@a.com', 'password' => bcrypt('x'), 'role' => 'officer']);
        $student = User::create(['name' => 'St', 'email' => 'st@a.com', 'password' => bcrypt('x'), 'role' => 'student']);

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($officer->hasRole('instructor')); // legacy officer -> instructor
        $this->assertTrue($student->hasRole('student'));

        $this->assertTrue($admin->can('access-admin'));
        $this->assertTrue($admin->can('manage-users'));
        $this->assertTrue($officer->can('access-admin'));
        $this->assertFalse($officer->can('manage-users')); // instructor can't manage users
        $this->assertFalse($student->can('access-admin'));
    }

    public function test_admin_middleware_uses_permission(): void
    {
        $instructor = User::create(['name' => 'In', 'email' => 'in@a.com', 'password' => bcrypt('x'), 'role' => 'instructor']);
        $student = User::create(['name' => 'St', 'email' => 'st@a.com', 'password' => bcrypt('x'), 'role' => 'student']);

        $this->actingAs($instructor)->get('/admin')->assertStatus(200);
        // Student lacks access-admin -> bounced to login
        $this->actingAs($student)->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_role_changes_resync(): void
    {
        $u = User::create(['name' => 'X', 'email' => 'x@a.com', 'password' => bcrypt('x'), 'role' => 'student']);
        $this->assertTrue($u->hasRole('student'));

        $u->update(['role' => 'admin']);
        $this->assertTrue($u->fresh()->hasRole('admin'));
        $this->assertFalse($u->fresh()->hasRole('student'));
    }
}
