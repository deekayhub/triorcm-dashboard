<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderVerification;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_create_verification()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('provider-verifications.store'), [
            'provider_id' => $provider->id,
            'type' => 'primary_source',
            'verification_source' => 'State Medical Board',
            'status' => 'pending',
        ])->assertRedirect();

        $this->assertDatabaseHas('provider_verifications', [
            'provider_id' => $provider->id,
            'type' => 'primary_source',
            'verification_source' => 'State Medical Board',
            'status' => 'pending',
        ]);
    }

    public function test_viewer_can_access_verifications()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('provider-verifications.store'), [
            'provider_id' => $provider->id,
            'type' => 'primary_source',
            'status' => 'pending',
        ])->assertRedirect();
    }

    public function test_viewer_can_delete_verification()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $verification = ProviderVerification::factory()->create();

        $this->actingAs($user)->delete(route('provider-verifications.destroy', $verification))
            ->assertRedirect();
    }

    public function test_verification_requires_valid_type_enum()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('provider-verifications.store'), [
            'provider_id' => $provider->id,
            'type' => 'invalid_type',
            'status' => 'pending',
        ])->assertSessionHasErrors('type');
    }

    public function test_verification_requires_valid_status_enum()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('provider-verifications.store'), [
            'provider_id' => $provider->id,
            'type' => 'primary_source',
            'status' => 'invalid_status',
        ])->assertSessionHasErrors('status');
    }

    public function test_verification_belongs_to_provider()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $otherProvider = Provider::factory()->create();

        $verification = ProviderVerification::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->patch(route('provider-verifications.update', $verification), [
            'provider_id' => $otherProvider->id,
            'status' => 'verified',
        ]);

        $this->assertEquals($provider->id, $verification->fresh()->provider_id);
    }
}
