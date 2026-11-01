<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderSpecialty;
use App\Models\Specialty;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderSpecialtyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_add_specialty()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $specialty = Specialty::factory()->create(['name' => 'Cardiology', 'is_active' => true]);

        $this->actingAs($user)->post(route('providers.specialties.store', $provider), [
            'specialty_id' => $specialty->id,
            'is_primary' => true,
            'subspecialty' => 'Interventional Cardiology',
            'is_board_certified' => true,
            'board_certification_date' => '2020-06-15',
            'is_active' => true,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_specialties', [
            'provider_id' => $provider->id,
            'specialty_id' => $specialty->id,
            'is_primary' => true,
            'subspecialty' => 'Interventional Cardiology',
            'is_board_certified' => true,
        ]);
    }

    public function test_viewer_cannot_add_specialty()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $specialty = Specialty::factory()->create();

        $this->actingAs($user)->post(route('providers.specialties.store', $provider), [
            'specialty_id' => $specialty->id,
        ])->assertForbidden();
    }

    public function test_only_one_primary_specialty_allowed()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $specialty1 = Specialty::factory()->create(['name' => 'Cardiology', 'is_active' => true]);
        $specialty2 = Specialty::factory()->create(['name' => 'Neurology', 'is_active' => true]);

        // Add first specialty as primary
        ProviderSpecialty::factory()->create([
            'provider_id' => $provider->id,
            'specialty_id' => $specialty1->id,
            'is_primary' => true,
            'is_active' => true,
        ]);

        // Add second specialty as primary - should unset the first
        $this->actingAs($user)->post(route('providers.specialties.store', $provider), [
            'specialty_id' => $specialty2->id,
            'is_primary' => true,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_specialties', [
            'id' => ProviderSpecialty::where('specialty_id', $specialty2->id)->first()->id,
            'is_primary' => true,
        ]);
        $this->assertDatabaseMissing('provider_specialties', [
            'specialty_id' => $specialty1->id,
            'is_primary' => true,
        ]);
        $this->assertDatabaseHas('provider_specialties', [
            'specialty_id' => $specialty1->id,
            'is_primary' => false,
        ]);
    }

    public function test_super_admin_can_update_specialty()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $specialty = Specialty::factory()->create();
        $providerSpecialty = ProviderSpecialty::factory()->create([
            'provider_id' => $provider->id,
            'specialty_id' => $specialty->id,
            'subspecialty' => 'Old Subspecialty',
            'is_board_certified' => false,
        ]);

        $this->actingAs($user)->patch(route('providers.specialties.update', [$provider, $providerSpecialty]), [
            'specialty_id' => $specialty->id,
            'subspecialty' => 'New Subspecialty',
            'is_board_certified' => true,
            'board_certification_date' => '2021-03-10',
            'is_active' => true,
        ])->assertSessionHas('success');

        $this->assertEquals('New Subspecialty', $providerSpecialty->fresh()->subspecialty);
        $this->assertTrue($providerSpecialty->fresh()->is_board_certified);
        $this->assertEquals('2021-03-10', $providerSpecialty->fresh()->board_certification_date->format('Y-m-d'));
    }

    public function test_super_admin_can_delete_specialty()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $specialty = Specialty::factory()->create();
        $providerSpecialty = ProviderSpecialty::factory()->create([
            'provider_id' => $provider->id,
            'specialty_id' => $specialty->id,
        ]);

        $this->actingAs($user)->delete(route('providers.specialties.destroy', [$provider, $providerSpecialty]))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('provider_specialties', [
            'id' => $providerSpecialty->id,
        ]);
    }

    public function test_viewer_cannot_delete_specialty()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $specialty = Specialty::factory()->create();
        $providerSpecialty = ProviderSpecialty::factory()->create([
            'provider_id' => $provider->id,
            'specialty_id' => $specialty->id,
        ]);

        $this->actingAs($user)->delete(route('providers.specialties.destroy', [$provider, $providerSpecialty]))
            ->assertForbidden();
    }

    public function test_specialty_validation_requires_specialty_id()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.specialties.store', $provider), [
            'specialty_id' => '',
        ])->assertSessionHasErrors('specialty_id');
    }

    public function test_specialty_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $specialty = Specialty::factory()->create();

        $this->actingAs($user)->post(route('providers.specialties.store', $provider), [
            'specialty_id' => $specialty->id,
            'is_primary' => false,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider specialty created',
            'event' => 'created',
        ]);
    }

    public function test_specialty_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $specialty = Specialty::factory()->create();
        $providerSpecialty = ProviderSpecialty::factory()->create([
            'provider_id' => $provider->id,
            'specialty_id' => $specialty->id,
        ]);

        $this->actingAs($user)->patch(route('providers.specialties.update', [$provider, $providerSpecialty]), [
            'specialty_id' => $specialty->id,
            'subspecialty' => 'Updated Subspecialty',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider specialty updated',
            'event' => 'updated',
        ]);
    }

    public function test_provider_profile_includes_specialties()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $specialty = Specialty::factory()->create();
        ProviderSpecialty::factory()->create([
            'provider_id' => $provider->id,
            'specialty_id' => $specialty->id,
        ]);

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('providers/Profile')
                ->has('provider'));
    }
}
