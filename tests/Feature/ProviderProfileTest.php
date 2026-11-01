<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderPersonalDetail;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_view_provider_profile()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('providers/Profile')
                ->has('provider'));
    }

    public function test_viewer_can_view_provider_profile()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('providers/Profile'));
    }

    public function test_guest_cannot_view_provider_profile()
    {
        $provider = Provider::factory()->create();

        $this->get(route('providers.profile', $provider))
            ->assertRedirect(route('login'));
    }

    public function test_viewer_cannot_update_personal_details()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)
            ->patch(route('providers.personal-details.update', $provider), [
                'first_name' => 'Updated',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_update_personal_details()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create([
            'first_name' => 'Original',
            'email' => 'original@example.com',
        ]);

        $response = $this->actingAs($user)->patch(route('providers.personal-details.update', $provider), [
            'first_name' => 'Updated',
            'last_name' => $provider->last_name,
            'email' => 'original@example.com',
            'phone' => '555-1234',
            'alternate_phone' => '555-5678',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip_code' => '62701',
            'country' => 'US',
            'languages' => ['English', 'Spanish'],
            'previous_names' => ['J. Smith'],
        ]);

        $response->assertSessionHas('success');

        $this->assertEquals('Updated', $provider->fresh()->first_name);
        $this->assertEquals('555-1234', $provider->fresh()->phone);
        $this->assertDatabaseHas('provider_personal_details', [
            'provider_id' => $provider->id,
            'alternate_phone' => '555-5678',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip_code' => '62701',
            'country' => 'US',
        ]);
        $this->assertEquals(['English', 'Spanish'], $provider->fresh()->personalDetail->languages);
        $this->assertEquals(['J. Smith'], $provider->fresh()->personalDetail->previous_names);
    }

    public function test_personal_details_validation_requires_first_name()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->patch(route('providers.personal-details.update', $provider), [
            'first_name' => '',
            'last_name' => $provider->last_name,
            'email' => $provider->email,
        ])->assertSessionHasErrors('first_name');
    }

    public function test_personal_details_validation_requires_email()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->patch(route('providers.personal-details.update', $provider), [
            'first_name' => $provider->first_name,
            'last_name' => $provider->last_name,
            'email' => '',
        ])->assertSessionHasErrors('email');
    }

    public function test_personal_details_duplicate_email_rejected()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider1 = Provider::factory()->create([
            'email' => 'provider1@example.com',
        ]);
        $provider2 = Provider::factory()->create([
            'email' => 'provider2@example.com',
        ]);

        $this->actingAs($user)->patch(route('providers.personal-details.update', $provider2), [
            'first_name' => $provider2->first_name,
            'last_name' => $provider2->last_name,
            'email' => 'provider1@example.com',
        ])->assertSessionHasErrors('email');
    }

    public function test_personal_details_activity_logged()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->patch(route('providers.personal-details.update', $provider), [
            'first_name' => $provider->first_name,
            'last_name' => $provider->last_name,
            'email' => $provider->email,
            'address' => '123 Main St',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider personal information updated',
            'event' => 'updated',
        ]);
    }

    public function test_personal_details_preserves_existing_data_on_partial_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'email' => 'test@example.com',
        ]);

        // First update with full data
        $this->actingAs($user)->patch(route('providers.personal-details.update', $provider), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'test@example.com',
            'address' => '123 Main St',
            'phone' => '555-1234',
        ]);

        // Then update only phone
        $this->actingAs($user)->patch(route('providers.personal-details.update', $provider), [
            'phone' => '555-9999',
        ]);

        $provider->refresh();
        $this->assertEquals('Updated', $provider->first_name);
        $this->assertEquals('123 Main St', $provider->personalDetail->address);
        $this->assertEquals('555-9999', $provider->phone);
    }
}
