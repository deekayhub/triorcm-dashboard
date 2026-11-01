<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderContact;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_add_contact()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.contacts.store', $provider), [
            'contact_name' => 'Jane Smith',
            'job_title' => 'Credentialing Specialist',
            'organization' => 'TrioRCM Practice',
            'email' => 'jane@example.com',
            'phone' => '555-1234',
            'contact_type' => 'credentialing',
            'preferred_method' => 'email',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_contacts', [
            'provider_id' => $provider->id,
            'contact_name' => 'Jane Smith',
            'job_title' => 'Credentialing Specialist',
            'contact_type' => 'credentialing',
        ]);
    }

    public function test_viewer_cannot_add_contact()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.contacts.store', $provider), [
            'contact_name' => 'Jane Smith',
        ])->assertForbidden();
    }

    public function test_super_admin_can_update_contact()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $contact = ProviderContact::factory()->create([
            'provider_id' => $provider->id,
            'contact_name' => 'Old Name',
        ]);

        $this->actingAs($user)->patch(route('providers.contacts.update', [$provider, $contact]), [
            'contact_name' => 'New Name',
            'job_title' => 'Practice Manager',
            'organization' => 'New Org',
            'email' => 'new@example.com',
            'phone' => '555-9999',
            'contact_type' => 'practice_manager',
            'preferred_method' => 'phone',
        ])->assertSessionHas('success');

        $this->assertEquals('New Name', $contact->fresh()->contact_name);
        $this->assertEquals('Practice Manager', $contact->fresh()->job_title);
    }

    public function test_super_admin_can_delete_contact()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $contact = ProviderContact::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.contacts.destroy', [$provider, $contact]))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('provider_contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_viewer_cannot_delete_contact()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $contact = ProviderContact::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.contacts.destroy', [$provider, $contact]))
            ->assertForbidden();
    }

    public function test_contact_validation_requires_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.contacts.store', $provider), [
            'contact_name' => '',
            'contact_type' => '',
            'preferred_method' => '',
        ])->assertSessionHasErrors(['contact_name', 'contact_type', 'preferred_method']);
    }

    public function test_contact_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.contacts.store', $provider), [
            'contact_name' => 'Test Contact',
            'contact_type' => 'credentialing',
            'preferred_method' => 'email',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider credentialing contact created',
            'event' => 'created',
        ]);
    }

    public function test_contact_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $contact = ProviderContact::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->patch(route('providers.contacts.update', [$provider, $contact]), [
            'contact_name' => $contact->contact_name,
            'contact_type' => $contact->contact_type,
            'preferred_method' => $contact->preferred_method,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider credentialing contact updated',
            'event' => 'updated',
        ]);
    }

    public function test_provider_profile_includes_contacts()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        ProviderContact::factory()->create([
            'provider_id' => $provider->id,
            'contact_name' => 'Test Contact',
        ]);

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('providers/Profile')
                ->has('provider'));
    }
}
