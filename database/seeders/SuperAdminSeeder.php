<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! Role::query()->where('name', 'super-admin')->where('guard_name', 'web')->exists()) {
            $this->call(RolePermissionSeeder::class);
        }

        $user = User::query()->firstOrCreate(
            ['email' => Str::lower((string) config('super_admin.email'))],
            [
                'name' => (string) config('super_admin.name'),
                'password' => (string) config('super_admin.password'),
            ],
        );

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $user->assignRole('super-admin');
    }
}
