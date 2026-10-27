<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private array $permissions = [
        'dashboard.view',
        'dashboard.export',
        'reports.view',
        'users.view',
        'users.create',
        'users.update',
        'users.delete',
        'roles.view',
        'roles.manage',
        'settings.manage',
    ];

    /**
     * @var array<string, list<string>>
     */
    private array $roles = [
        'super-admin' => [
            'dashboard.view',
            'dashboard.export',
            'reports.view',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.manage',
            'settings.manage',
        ],
        'admin' => [
            'dashboard.view',
            'dashboard.export',
            'reports.view',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.manage',
            'settings.manage',
        ],
        'manager' => [
            'dashboard.view',
            'dashboard.export',
            'reports.view',
            'users.view',
            'users.update',
        ],
        'viewer' => [
            'dashboard.view',
            'reports.view',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach ($this->roles as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }

        if (! User::query()->whereHas('roles')->exists()) {
            $users = User::query()->orderBy('id')->get();

            $users->first()?->assignRole('super-admin');
            $users->skip(1)->each(fn (User $user) => $user->assignRole('viewer'));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
