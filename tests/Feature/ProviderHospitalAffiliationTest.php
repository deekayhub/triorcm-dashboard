<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderHospitalAffiliation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderHospitalAffiliationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_add_hospital_affiliation()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.hospital-affiliations.store', $provider), [
            'facility_name' => 'General Hospital',
            'department' => 'Cardiology',
            'affiliation_type' => 'admitting',
            'start_date' => '2020-01-01',
            'end_date' => '2025-12-31',
            'status' => 'active',
            'contact_phone' => '555-1234',
            'contact_email' => 'admin@generalhospital.com',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_hospital_affiliations', [
            'provider_id' => $provider->id,
            'facility_name' => 'General Hospital',
            'department' => 'Cardiology',
            'affiliation_type' => 'admitting',
            'status' => 'active',
        ]);
    }

    public function test_viewer_cannot_add_hospital_affiliation()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.hospital-affiliations.store', $provider), [
            'facility_name' => 'General Hospital',
        ])->assertForbidden();
    }

    public function test_super_admin_can_update_hospital_affiliation()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $affiliation = ProviderHospitalAffiliation::factory()->create([
            'provider_id' => $provider->id,
            'facility_name' => 'Old Hospital',
            'status' => 'inactive',
        ]);

        $this->actingAs($user)->patch(route('providers.hospital-affiliations.update', [$provider, $affiliation]), [
            'facility_name' => 'New Hospital',
            'department' => 'Emergency',
            'affiliation_type' => 'consulting',
            'start_date' => '2021-01-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'contact_phone' => '555-9999',
            'contact_email' => 'new@hospital.com',
        ])->assertSessionHas('success');

        $this->assertEquals('New Hospital', $affiliation->fresh()->facility_name);
        $this->assertEquals('Emergency', $affiliation->fresh()->department);
        $this->assertEquals('active', $affiliation->fresh()->status);
    }

    public function test_super_admin_can_delete_hospital_affiliation()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $affiliation = ProviderHospitalAffiliation::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.hospital-affiliations.destroy', [$provider, $affiliation]))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('provider_hospital_affiliations', [
            'id' => $affiliation->id,
        ]);
    }

    public function test_viewer_cannot_delete_hospital_affiliation()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $affiliation = ProviderHospitalAffiliation::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.hospital-affiliations.destroy', [$provider, $affiliation]))
            ->assertForbidden();
    }

    public function test_hospital_affiliation_validation_requires_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.hospital-affiliations.store', $provider), [
            'facility_name' => '',
            'affiliation_type' => '',
            'status' => '',
        ])->assertSessionHasErrors(['facility_name', 'affiliation_type', 'status']);
    }

    public function test_end_date_cannot_be_before_start_date()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.hospital-affiliations.store', $provider), [
            'facility_name' => 'Test Hospital',
            'affiliation_type' => 'admitting',
            'status' => 'active',
            'start_date' => '2025-01-01',
            'end_date' => '2024-01-01',
        ])->assertSessionHasErrors('end_date');
    }

    public function test_hospital_affiliation_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.hospital-affiliations.store', $provider), [
            'facility_name' => 'Test Hospital',
            'affiliation_type' => 'admitting',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider hospital affiliation created',
            'event' => 'created',
        ]);
    }

    public function test_hospital_affiliation_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $affiliation = ProviderHospitalAffiliation::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->patch(route('providers.hospital-affiliations.update', [$provider, $affiliation]), [
            'facility_name' => $affiliation->facility_name,
            'affiliation_type' => $affiliation->affiliation_type,
            'status' => $affiliation->status,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider hospital affiliation updated',
            'event' => 'updated',
        ]);
    }

    public function test_provider_profile_includes_hospital_affiliations()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        ProviderHospitalAffiliation::factory()->create([
            'provider_id' => $provider->id,
            'facility_name' => 'Test Hospital',
        ]);

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('providers/Profile')
                ->has('provider'));
    }
}
