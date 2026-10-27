<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_verified_super_admin()
    {
        $this->seed(SuperAdminSeeder::class);

        $email = Str::lower((string) config('super_admin.email'));
        $user = User::query()->where('email', $email)->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue($user->hasRole('super-admin'));
    }

    public function test_it_is_idempotent()
    {
        $this->seed(SuperAdminSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $email = Str::lower((string) config('super_admin.email'));

        $this->assertSame(
            1,
            User::query()->where('email', $email)->count(),
        );
    }
}
