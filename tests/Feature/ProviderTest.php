<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_guests_cannot_access_providers_index()
    {
        $this->get(route('providers.index'))
            ->assertRedirect(route('login'));
    }

    public function test_viewer_can_view_providers_index()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');
        $user->givePermissionTo('providers.viewAny');

        $this->actingAs($user)
            ->get(route('providers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('providers/Index')
                ->has('stats')
                ->has('providers'));
    }

    public function test_super_admin_can_view_providers_index()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get(route('providers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('providers/Index'));
    }

    public function test_super_admin_can_create_provider()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $team = Team::factory()->create([
            'name' => 'Credentialing Team',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('providers.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'npi' => '1234567890',
            'phone' => '555-1234',
            'provider_type' => 'physician',
            'specialty' => 'Cardiology',
            'status' => 'active',
            'team_id' => $team->id,
        ]);

        $response->assertRedirect(route('providers.show', Provider::first()->id));

        $provider = Provider::first();
        $this->assertEquals('John', $provider->first_name);
        $this->assertEquals('Doe', $provider->last_name);
        $this->assertEquals('john.doe@example.com', $provider->email);
        $this->assertEquals('1234567890', $provider->npi);
        $this->assertEquals($team->id, $provider->team_id);
        $this->assertEquals($user->id, $provider->created_by);
        $this->assertEquals($user->id, $provider->updated_by);
        $this->assertEquals('active', $provider->status);
    }

    public function test_viewer_cannot_create_provider()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $response = $this->actingAs($user)->post(route('providers.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'provider_type' => 'physician',
            'status' => 'active',
        ]);

        $response->assertForbidden();
    }

    public function test_duplicate_npi_is_rejected()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        Provider::factory()->create([
            'npi' => '1234567890',
            'email' => 'existing@example.com',
        ]);

        $response = $this->actingAs($user)->post(route('providers.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'npi' => '1234567890',
            'provider_type' => 'physician',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('npi');
    }

    public function test_duplicate_email_is_rejected()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        Provider::factory()->create([
            'npi' => '1111111111',
            'email' => 'existing@example.com',
        ]);

        $response = $this->actingAs($user)->post(route('providers.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'existing@example.com',
            'provider_type' => 'physician',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_super_admin_can_view_provider_show()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('providers.show', $provider))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('providers/Show')
                ->has('provider'));
    }

    public function test_super_admin_can_edit_provider()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)
            ->get(route('providers.edit', $provider))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('providers/Edit')
                ->has('provider')
                ->has('teams')
                ->has('users'));
    }

    public function test_super_admin_can_update_provider()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create([
            'first_name' => 'Original',
        ]);

        $this->actingAs($user)->put(route('providers.update', $provider), [
            'first_name' => 'Updated',
            'last_name' => $provider->last_name,
            'email' => $provider->email,
            'provider_type' => $provider->provider_type,
            'status' => $provider->status,
        ]);

        $this->assertEquals('Updated', $provider->fresh()->first_name);
        $this->assertEquals($user->id, $provider->fresh()->updated_by);
    }

    public function test_provider_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)->post(route('providers.store'), [
            'first_name' => 'Activity',
            'last_name' => 'Test',
            'email' => 'activity@example.com',
            'provider_type' => 'physician',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider created',
            'event' => 'created',
        ]);
    }

    public function test_provider_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->put(route('providers.update', $provider), [
            'first_name' => 'Updated Name',
            'last_name' => $provider->last_name,
            'email' => $provider->email,
            'provider_type' => $provider->provider_type,
            'status' => $provider->status,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider updated',
            'event' => 'updated',
        ]);
    }
}
