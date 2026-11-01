<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderLocation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderLocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_add_location()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.locations.store', $provider), [
            'location_name' => 'Main Office',
            'practice_name' => 'TrioRCM Practice',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip_code' => '62701',
            'country' => 'US',
            'phone' => '555-1234',
            'fax' => '555-5678',
            'email' => 'office@example.com',
            'is_primary' => true,
            'is_billing_address' => true,
            'is_active' => true,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_locations', [
            'provider_id' => $provider->id,
            'location_name' => 'Main Office',
            'practice_name' => 'TrioRCM Practice',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip_code' => '62701',
            'is_primary' => true,
            'is_billing_address' => true,
        ]);
    }

    public function test_viewer_cannot_add_location()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.locations.store', $provider), [
            'location_name' => 'Main Office',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip_code' => '62701',
        ])->assertForbidden();
    }

    public function test_only_one_primary_location_allowed()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        // Add first location as primary
        ProviderLocation::factory()->create([
            'provider_id' => $provider->id,
            'location_name' => 'First Location',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // Add second location as primary - should unset the first
        $this->actingAs($user)->post(route('providers.locations.store', $provider), [
            'location_name' => 'Second Location',
            'address' => '456 Oak Ave',
            'city' => 'Shelbyville',
            'state' => 'IL',
            'zip_code' => '62565',
            'is_primary' => true,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_locations', [
            'location_name' => 'Second Location',
            'is_primary' => true,
        ]);
        $this->assertDatabaseHas('provider_locations', [
            'location_name' => 'First Location',
            'is_primary' => false,
        ]);
    }

    public function test_super_admin_can_update_location()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $location = ProviderLocation::factory()->create([
            'provider_id' => $provider->id,
            'location_name' => 'Old Name',
            'city' => 'Old City',
        ]);

        $this->actingAs($user)->patch(route('providers.locations.update', [$provider, $location]), [
            'location_name' => 'New Name',
            'practice_name' => 'New Practice',
            'address' => '789 New St',
            'city' => 'New City',
            'state' => 'NY',
            'zip_code' => '10001',
            'country' => 'US',
            'phone' => '555-9999',
            'fax' => '',
            'email' => 'new@example.com',
            'is_primary' => false,
            'is_billing_address' => false,
            'is_active' => true,
        ])->assertSessionHas('success');

        $this->assertEquals('New Name', $location->fresh()->location_name);
        $this->assertEquals('New City', $location->fresh()->city);
        $this->assertEquals('NY', $location->fresh()->state);
    }

    public function test_super_admin_can_delete_location()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $location = ProviderLocation::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.locations.destroy', [$provider, $location]))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('provider_locations', [
            'id' => $location->id,
        ]);
    }

    public function test_viewer_cannot_delete_location()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $location = ProviderLocation::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.locations.destroy', [$provider, $location]))
            ->assertForbidden();
    }

    public function test_location_validation_requires_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.locations.store', $provider), [
            'location_name' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
        ])->assertSessionHasErrors(['location_name', 'address', 'city', 'state', 'zip_code']);
    }

    public function test_effective_end_date_cannot_be_before_start_date()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.locations.store', $provider), [
            'location_name' => 'Test Location',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip_code' => '12345',
            'effective_start_date' => '2025-01-01',
            'effective_end_date' => '2024-01-01',
        ])->assertSessionHasErrors('effective_end_date');
    }

    public function test_location_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.locations.store', $provider), [
            'location_name' => 'Test Location',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip_code' => '12345',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider location created',
            'event' => 'created',
        ]);
    }

    public function test_location_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $location = ProviderLocation::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->patch(route('providers.locations.update', [$provider, $location]), [
            'location_name' => $location->location_name,
            'address' => $location->address,
            'city' => $location->city,
            'state' => $location->state,
            'zip_code' => $location->zip_code,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider location updated',
            'event' => 'updated',
        ]);
    }

    public function test_provider_profile_includes_locations()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        ProviderLocation::factory()->create([
            'provider_id' => $provider->id,
            'location_name' => 'Test Location',
        ]);

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('providers/Profile')
                ->has('provider'));
    }
}
