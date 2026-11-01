<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderInsurance;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderInsuranceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_add_insurance()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.insurances.store', $provider), [
            'carrier' => 'Blue Cross Blue Shield',
            'policy_number' => 'POL123456789',
            'coverage_type' => 'occurrence',
            'per_claim_amount' => '2000000',
            'aggregate_amount' => '5000000',
            'effective_date' => '2025-01-01',
            'expiration_date' => '2026-12-31',
            'agent_contact' => 'John Agent',
            'agent_phone' => '555-1234',
            'verification_status' => 'verified',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_insurances', [
            'provider_id' => $provider->id,
            'carrier' => 'Blue Cross Blue Shield',
            'policy_number' => 'POL123456789',
            'coverage_type' => 'occurrence',
            'verification_status' => 'verified',
        ]);
    }

    public function test_viewer_cannot_add_insurance()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.insurances.store', $provider), [
            'carrier' => 'Test Carrier',
            'policy_number' => 'POL123',
        ])->assertForbidden();
    }

    public function test_super_admin_can_update_insurance()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $insurance = ProviderInsurance::factory()->create([
            'provider_id' => $provider->id,
            'carrier' => 'Old Carrier',
            'verification_status' => 'unverified',
        ]);

        $this->actingAs($user)->patch(route('providers.insurances.update', [$provider, $insurance]), [
            'carrier' => 'New Carrier',
            'policy_number' => $insurance->policy_number,
            'coverage_type' => $insurance->coverage_type,
            'per_claim_amount' => '3000000',
            'aggregate_amount' => '7000000',
            'effective_date' => '2025-01-01',
            'expiration_date' => '2026-12-31',
            'agent_contact' => 'Agent Name',
            'agent_phone' => '555-9999',
            'verification_status' => 'verified',
        ])->assertSessionHas('success');

        $this->assertEquals('New Carrier', $insurance->fresh()->carrier);
        $this->assertEquals('3000000.00', $insurance->fresh()->per_claim_amount);
        $this->assertEquals('verified', $insurance->fresh()->verification_status);
    }

    public function test_super_admin_can_delete_insurance()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $insurance = ProviderInsurance::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.insurances.destroy', [$provider, $insurance]))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('provider_insurances', [
            'id' => $insurance->id,
        ]);
    }

    public function test_viewer_cannot_delete_insurance()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $insurance = ProviderInsurance::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.insurances.destroy', [$provider, $insurance]))
            ->assertForbidden();
    }

    public function test_insurance_validation_requires_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.insurances.store', $provider), [
            'carrier' => '',
            'policy_number' => '',
            'coverage_type' => '',
            'effective_date' => '',
            'expiration_date' => '',
            'verification_status' => '',
        ])->assertSessionHasErrors(['carrier', 'policy_number', 'coverage_type', 'effective_date', 'expiration_date', 'verification_status']);
    }

    public function test_expiration_date_cannot_be_before_effective_date()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.insurances.store', $provider), [
            'carrier' => 'Test Carrier',
            'policy_number' => 'POLTEST123',
            'coverage_type' => 'claims_made',
            'effective_date' => '2025-01-01',
            'expiration_date' => '2024-01-01',
            'verification_status' => 'pending',
        ])->assertSessionHasErrors('expiration_date');
    }

    public function test_insurance_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.insurances.store', $provider), [
            'carrier' => 'Test Carrier',
            'policy_number' => 'POLTEST123',
            'coverage_type' => 'claims_made',
            'effective_date' => '2025-01-01',
            'expiration_date' => '2026-01-01',
            'verification_status' => 'pending',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider insurance created',
            'event' => 'created',
        ]);
    }

    public function test_insurance_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $insurance = ProviderInsurance::factory()->create([
            'provider_id' => $provider->id,
            'verification_status' => 'unverified',
            'carrier' => 'Original Carrier',
        ]);

        $this->actingAs($user)->patch(route('providers.insurances.update', [$provider, $insurance]), [
            'carrier' => 'Updated Carrier',
            'policy_number' => 'UPDATED-POL-123',
            'coverage_type' => 'claims_made',
            'per_claim_amount' => '5000000',
            'aggregate_amount' => '10000000',
            'effective_date' => '2026-01-01',
            'expiration_date' => '2027-01-01',
            'agent_contact' => 'New Agent',
            'agent_phone' => '555-0000',
            'verification_status' => 'verified',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider insurance updated',
            'event' => 'updated',
        ]);
    }

    public function test_provider_profile_includes_insurances()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        ProviderInsurance::factory()->create([
            'provider_id' => $provider->id,
            'carrier' => 'Test Carrier',
        ]);

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('providers/Profile')
                ->has('provider'));
    }
}
