<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderLicense;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderLicenseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_create_license()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('provider-licenses.store'), [
            'provider_id' => $provider->id,
            'license_number' => 'MD1234567',
            'license_type' => 'md',
            'state' => 'CA',
            'issue_date' => '2020-01-01',
            'expiration_date' => '2027-01-01',
            'status' => 'active',
            'issuing_board' => 'State Medical Board',
        ])->assertRedirect();

        $this->assertDatabaseHas('provider_licenses', [
            'provider_id' => $provider->id,
            'license_number' => 'MD1234567',
            'license_type' => 'md',
            'state' => 'CA',
            'status' => 'active',
        ]);
    }

    public function test_viewer_can_create_license()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('provider-licenses.store'), [
            'provider_id' => $provider->id,
            'license_number' => 'MD1234567',
            'license_type' => 'md',
            'state' => 'CA',
            'issue_date' => '2020-01-01',
            'expiration_date' => '2027-01-01',
            'status' => 'active',
        ])->assertRedirect();
    }

    public function test_license_requires_required_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('provider-licenses.store'), [
            'provider_id' => $provider->id,
            'license_number' => '',
            'license_type' => '',
            'state' => '',
            'issue_date' => '',
            'expiration_date' => '',
            'status' => '',
        ])->assertSessionHasErrors([
            'license_number',
            'license_type',
            'state',
            'issue_date',
            'expiration_date',
            'status',
        ]);
    }

    public function test_license_type_must_be_valid_enum()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('provider-licenses.store'), [
            'provider_id' => $provider->id,
            'license_number' => 'MD1234567',
            'license_type' => 'invalid_type',
            'state' => 'CA',
            'issue_date' => '2020-01-01',
            'expiration_date' => '2027-01-01',
            'status' => 'active',
        ])->assertSessionHasErrors('license_type');
    }

    public function test_license_status_must_be_valid_enum()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('provider-licenses.store'), [
            'provider_id' => $provider->id,
            'license_number' => 'MD1234567',
            'license_type' => 'md',
            'state' => 'CA',
            'issue_date' => '2020-01-01',
            'expiration_date' => '2027-01-01',
            'status' => 'invalid_status',
        ])->assertSessionHasErrors('status');
    }

    public function test_license_number_must_be_unique()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        ProviderLicense::factory()->create([
            'provider_id' => $provider->id,
            'license_number' => 'EXISTING123',
        ]);

        $this->actingAs($user)->post(route('provider-licenses.store'), [
            'provider_id' => $provider->id,
            'license_number' => 'EXISTING123',
            'license_type' => 'md',
            'state' => 'TX',
            'issue_date' => '2020-01-01',
            'expiration_date' => '2027-01-01',
            'status' => 'active',
        ])->assertSessionHasErrors('license_number');
    }

    public function test_super_admin_can_update_license()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $license = ProviderLicense::factory()->create([
            'provider_id' => $provider->id,
            'license_number' => 'OLD123',
            'status' => 'active',
        ]);

        $this->actingAs($user)->patch(route('provider-licenses.update', $license), [
            'license_number' => 'NEW123',
            'license_type' => 'md',
            'state' => 'CA',
            'issue_date' => '2020-01-01',
            'expiration_date' => '2027-01-01',
            'status' => 'expired',
            'issuing_board' => 'State Medical Board',
        ])->assertRedirect();

        $this->assertEquals('NEW123', $license->fresh()->license_number);
        $this->assertEquals('expired', $license->fresh()->status);
    }

    public function test_super_admin_can_delete_license()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $license = ProviderLicense::factory()->create();

        $this->actingAs($user)->delete(route('provider-licenses.destroy', $license))
            ->assertRedirect();

        $this->assertDatabaseMissing('provider_licenses', [
            'id' => $license->id,
        ]);
    }

    public function test_super_admin_can_view_license()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $license = ProviderLicense::factory()->create();

        $this->actingAs($user)->get(route('provider-licenses.show', $license))
            ->assertOk();
    }

    public function test_viewer_can_view_license()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $license = ProviderLicense::factory()->create();

        $this->actingAs($user)->get(route('provider-licenses.show', $license))
            ->assertOk();
    }

    public function test_license_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('provider-licenses.store'), [
            'provider_id' => $provider->id,
            'license_number' => 'MD1234567',
            'license_type' => 'md',
            'state' => 'CA',
            'issue_date' => '2020-01-01',
            'expiration_date' => '2027-01-01',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider license created',
        ]);
    }

    public function test_license_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $license = ProviderLicense::factory()->active()->create();

        $this->actingAs($user)->patch(route('provider-licenses.update', $license), [
            'license_number' => $license->license_number,
            'license_type' => $license->license_type,
            'state' => $license->state,
            'issue_date' => $license->issue_date->format('Y-m-d'),
            'expiration_date' => $license->expiration_date->format('Y-m-d'),
            'status' => 'expired',
            'issuing_board' => $license->issuing_board,
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider license updated',
        ]);
    }

    public function test_license_belongs_to_provider()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $otherProvider = Provider::factory()->create();

        $license = ProviderLicense::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->patch(route('provider-licenses.update', $license), [
            'provider_id' => $otherProvider->id,
            'status' => 'expired',
        ]);

        $this->assertEquals($provider->id, $license->fresh()->provider_id);
    }
}
