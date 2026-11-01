<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderIdentifier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderIdentifierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_add_identifier()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.identifiers.store', $provider), [
            'identifier_type' => 'dea',
            'identifier_value' => 'DEA123456',
            'issuing_state' => 'CA',
            'issue_date' => '2020-01-01',
            'expiration_date' => '2025-12-31',
            'verification_status' => 'pending',
            'verification_notes' => 'Pending verification',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_identifiers', [
            'provider_id' => $provider->id,
            'identifier_type' => 'dea',
            'identifier_value' => 'DEA123456',
            'issuing_state' => 'CA',
            'verification_status' => 'pending',
        ]);
    }

    public function test_viewer_cannot_add_identifier()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.identifiers.store', $provider), [
            'identifier_type' => 'dea',
            'identifier_value' => 'DEA123456',
        ])->assertForbidden();
    }

    public function test_duplicate_identifier_value_is_rejected()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        ProviderIdentifier::factory()->create([
            'provider_id' => $provider->id,
            'identifier_type' => 'dea',
            'identifier_value' => 'EXISTING123',
        ]);

        $this->actingAs($user)->post(route('providers.identifiers.store', $provider), [
            'identifier_type' => 'dea',
            'identifier_value' => 'EXISTING123',
        ])->assertSessionHasErrors('identifier_value');
    }

    public function test_super_admin_can_update_identifier()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $identifier = ProviderIdentifier::factory()->create([
            'provider_id' => $provider->id,
            'identifier_value' => 'OLD123',
            'verification_status' => 'unverified',
        ]);

        $this->actingAs($user)->patch(route('providers.identifiers.update', [$provider, $identifier]), [
            'identifier_type' => 'dea',
            'identifier_value' => 'NEW456',
            'issuing_state' => 'NY',
            'issue_date' => '2021-01-01',
            'expiration_date' => '2026-12-31',
            'verification_status' => 'verified',
            'verification_notes' => 'Verified',
        ])->assertSessionHas('success');

        $this->assertEquals('NEW456', $identifier->fresh()->identifier_value);
        $this->assertEquals('NY', $identifier->fresh()->issuing_state);
        $this->assertEquals('verified', $identifier->fresh()->verification_status);
    }

    public function test_super_admin_can_delete_identifier()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $identifier = ProviderIdentifier::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.identifiers.destroy', [$provider, $identifier]))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('provider_identifiers', [
            'id' => $identifier->id,
        ]);
    }

    public function test_viewer_cannot_delete_identifier()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $identifier = ProviderIdentifier::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.identifiers.destroy', [$provider, $identifier]))
            ->assertForbidden();
    }

    public function test_identifier_validation_requires_type_and_value()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.identifiers.store', $provider), [
            'identifier_type' => '',
            'identifier_value' => '',
        ])->assertSessionHasErrors(['identifier_type', 'identifier_value']);
    }

    public function test_expiration_date_cannot_be_before_issue_date()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.identifiers.store', $provider), [
            'identifier_type' => 'dea',
            'identifier_value' => 'DEATEST123',
            'issue_date' => '2025-01-01',
            'expiration_date' => '2020-01-01',
        ])->assertSessionHasErrors('expiration_date');
    }

    public function test_identifier_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.identifiers.store', $provider), [
            'identifier_type' => 'dea',
            'identifier_value' => 'DEAACTIVITY',
            'verification_status' => 'pending',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider identifier created',
            'event' => 'created',
        ]);
    }

    public function test_identifier_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $identifier = ProviderIdentifier::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->patch(route('providers.identifiers.update', [$provider, $identifier]), [
            'identifier_type' => 'dea',
            'identifier_value' => $identifier->identifier_value,
            'verification_status' => 'verified',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider identifier updated',
            'event' => 'updated',
        ]);
    }

    public function test_provider_profile_includes_identifiers()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        ProviderIdentifier::factory()->create([
            'provider_id' => $provider->id,
            'identifier_type' => 'dea',
            'identifier_value' => 'DEATEST',
        ]);

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('providers/Profile')
                ->has('provider'));
    }
}
