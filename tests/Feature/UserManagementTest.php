<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admins_can_view_the_user_directory()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/Index')
                ->has('users')
                ->has('roles', 4));
    }

    public function test_managers_can_view_users_but_not_roles()
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $this->actingAs($manager)->get(route('users.index'))->assertOk();
        $this->actingAs($manager)->get(route('roles.index'))->assertForbidden();
    }

    public function test_viewers_cannot_view_users_or_roles()
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)->get(route('users.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('roles.index'))->assertForbidden();
    }

    public function test_super_admins_can_create_users()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->post(route('users.store'), [
            'name' => 'New Manager',
            'email' => 'new.manager@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'manager',
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'new.manager@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('manager'));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_admins_cannot_create_users()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'viewer',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
    }

    public function test_super_admins_can_view_the_permission_matrix()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('roles/Index')
                ->has('roles', 4)
                ->has('permissions', 10));
    }
}
