<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderEmployment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderEmploymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_add_employment()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.employments.store', $provider), [
            'employer' => 'General Hospital',
            'position' => 'Physician',
            'employment_type' => 'full_time',
            'address' => '123 Hospital Dr',
            'start_date' => '2020-01-01',
            'end_date' => '2025-12-31',
            'is_current' => false,
            'gap_explanation' => '',
            'verification_status' => 'verified',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_employments', [
            'provider_id' => $provider->id,
            'employer' => 'General Hospital',
            'position' => 'Physician',
            'employment_type' => 'full_time',
            'verification_status' => 'verified',
        ]);
    }

    public function test_viewer_cannot_add_employment()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.employments.store', $provider), [
            'employer' => 'General Hospital',
            'position' => 'Physician',
        ])->assertForbidden();
    }

    public function test_only_one_current_employment_allowed()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        // Add first employment as current
        ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
            'employer' => 'First Employer',
            'is_current' => true,
        ]);

        // Add second employment as current - should unset the first
        $this->actingAs($user)->post(route('providers.employments.store', $provider), [
            'employer' => 'Second Employer',
            'position' => 'Physician',
            'employment_type' => 'full_time',
            'start_date' => '2020-01-01',
            'is_current' => true,
            'verification_status' => 'pending',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_employments', [
            'employer' => 'Second Employer',
            'is_current' => true,
        ]);
        $this->assertDatabaseHas('provider_employments', [
            'employer' => 'First Employer',
            'is_current' => false,
        ]);
    }

    public function test_super_admin_can_update_employment()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $employment = ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
            'employer' => 'Old Employer',
        ]);

        $this->actingAs($user)->patch(route('providers.employments.update', [$provider, $employment]), [
            'employer' => 'New Employer',
            'position' => 'Senior Physician',
            'employment_type' => 'full_time',
            'address' => '456 New St',
            'start_date' => '2021-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
            'gap_explanation' => '',
            'verification_status' => 'verified',
        ])->assertSessionHas('success');

        $this->assertEquals('New Employer', $employment->fresh()->employer);
        $this->assertEquals('Senior Physician', $employment->fresh()->position);
        $this->assertTrue($employment->fresh()->is_current);
    }

    public function test_super_admin_can_delete_employment()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $employment = ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.employments.destroy', [$provider, $employment]))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('provider_employments', [
            'id' => $employment->id,
        ]);
    }

    public function test_viewer_cannot_delete_employment()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $employment = ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.employments.destroy', [$provider, $employment]))
            ->assertForbidden();
    }

    public function test_employment_validation_requires_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.employments.store', $provider), [
            'employer' => '',
            'position' => '',
            'employment_type' => '',
            'start_date' => '',
            'verification_status' => '',
        ])->assertSessionHasErrors(['employer', 'position', 'employment_type', 'start_date', 'verification_status']);
    }

    public function test_end_date_cannot_be_before_start_date()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.employments.store', $provider), [
            'employer' => 'Test Employer',
            'position' => 'Physician',
            'employment_type' => 'full_time',
            'start_date' => '2025-01-01',
            'end_date' => '2024-01-01',
            'verification_status' => 'pending',
        ])->assertSessionHasErrors('end_date');
    }

    public function test_employment_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.employments.store', $provider), [
            'employer' => 'Test Employer',
            'position' => 'Physician',
            'employment_type' => 'full_time',
            'start_date' => '2025-01-01',
            'verification_status' => 'pending',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider employment created',
            'event' => 'created',
        ]);
    }

    public function test_employment_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $employment = ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
            'verification_status' => 'unverified',
            'employer' => 'Original Employer',
        ]);

        $this->actingAs($user)->patch(route('providers.employments.update', [$provider, $employment]), [
            'employer' => 'Updated Employer',
            'position' => 'Senior Physician',
            'employment_type' => 'full_time',
            'address' => '456 New St',
            'start_date' => '2021-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
            'gap_explanation' => '',
            'verification_status' => 'verified',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider employment updated',
            'event' => 'updated',
        ]);
    }

    public function test_viewer_cannot_update_employment()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $employment = ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
            'employer' => 'Original Employer',
        ]);

        $this->actingAs($user)->patch(route('providers.employments.update', [$provider, $employment]), [
            'employer' => 'Changed Employer',
            'position' => 'Physician',
            'employment_type' => 'full_time',
            'start_date' => '2021-01-01',
            'verification_status' => 'verified',
        ])->assertForbidden();

        $this->assertEquals('Original Employer', $employment->fresh()->employer);
    }

    public function test_updating_current_employment_unsets_the_previous_current_record()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $current = ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
            'employer' => 'Current Employer',
            'is_current' => true,
        ]);

        $other = ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
            'employer' => 'Other Employer',
            'is_current' => false,
        ]);

        $this->actingAs($user)->patch(route('providers.employments.update', [$provider, $other]), [
            'employer' => 'Other Employer',
            'position' => 'Physician',
            'employment_type' => 'full_time',
            'start_date' => '2021-01-01',
            'is_current' => true,
            'verification_status' => 'verified',
        ])->assertSessionHas('success');

        $this->assertFalse($current->fresh()->is_current);
        $this->assertTrue($other->fresh()->is_current);
    }

    public function test_employment_cannot_be_accessed_through_another_provider()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $otherProvider = Provider::factory()->create();

        $employment = ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->patch(route('providers.employments.update', [$otherProvider, $employment]), [
            'employer' => 'Hijacked Employer',
            'position' => 'Physician',
            'employment_type' => 'full_time',
            'start_date' => '2021-01-01',
            'verification_status' => 'verified',
        ])->assertNotFound();

        $this->actingAs($user)
            ->delete(route('providers.employments.destroy', [$otherProvider, $employment]))
            ->assertNotFound();

        $this->assertNull($employment->fresh()->deleted_at);
    }

    public function test_provider_id_cannot_be_changed_when_updating_employment()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $otherProvider = Provider::factory()->create();

        $employment = ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->patch(route('providers.employments.update', [$provider, $employment]), [
            'provider_id' => $otherProvider->id,
            'employer' => 'Updated Employer',
            'position' => 'Physician',
            'employment_type' => 'full_time',
            'start_date' => '2021-01-01',
            'verification_status' => 'verified',
        ])->assertSessionHas('success');

        $this->assertEquals($provider->id, $employment->fresh()->provider_id);
    }

    public function test_provider_profile_includes_employments()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        ProviderEmployment::factory()->create([
            'provider_id' => $provider->id,
            'employer' => 'Test Employer',
        ]);

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('providers/Profile')
                ->has('provider'));
    }
}
