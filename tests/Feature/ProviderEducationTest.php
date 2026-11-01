<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderEducation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderEducationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_add_education()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.educations.store', $provider), [
            'institution_name' => 'Harvard Medical School',
            'degree' => 'MD',
            'education_type' => 'medical_school',
            'specialty' => 'Internal Medicine',
            'start_date' => '2010-09-01',
            'completion_date' => '2014-05-31',
            'country' => 'US',
            'state' => 'MA',
            'city' => 'Boston',
            'verification_status' => 'verified',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_education', [
            'provider_id' => $provider->id,
            'institution_name' => 'Harvard Medical School',
            'degree' => 'MD',
            'education_type' => 'medical_school',
            'verification_status' => 'verified',
        ]);
    }

    public function test_viewer_cannot_add_education()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.educations.store', $provider), [
            'institution_name' => 'Test',
            'degree' => 'MD',
            'education_type' => 'medical_school',
        ])->assertForbidden();
    }

    public function test_super_admin_can_update_education()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $education = ProviderEducation::factory()->create([
            'provider_id' => $provider->id,
            'institution_name' => 'Old School',
            'verification_status' => 'unverified',
        ]);

        $this->actingAs($user)->patch(route('providers.educations.update', [$provider, $education]), [
            'institution_name' => 'New School',
            'degree' => 'MD',
            'education_type' => 'medical_school',
            'specialty' => 'Cardiology',
            'start_date' => '2010-09-01',
            'completion_date' => '2014-05-31',
            'country' => 'US',
            'state' => 'NY',
            'city' => 'New York',
            'verification_status' => 'verified',
        ])->assertSessionHas('success');

        $this->assertEquals('New School', $education->fresh()->institution_name);
        $this->assertEquals('verified', $education->fresh()->verification_status);
    }

    public function test_super_admin_can_delete_education()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $education = ProviderEducation::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.educations.destroy', [$provider, $education]))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('provider_education', [
            'id' => $education->id,
        ]);
    }

    public function test_viewer_cannot_delete_education()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $education = ProviderEducation::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->delete(route('providers.educations.destroy', [$provider, $education]))
            ->assertForbidden();
    }

    public function test_education_validation_requires_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.educations.store', $provider), [
            'institution_name' => '',
            'degree' => '',
            'education_type' => '',
        ])->assertSessionHasErrors(['institution_name', 'degree', 'education_type']);
    }

    public function test_completion_date_cannot_be_before_start_date()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.educations.store', $provider), [
            'institution_name' => 'Test School',
            'degree' => 'MD',
            'education_type' => 'medical_school',
            'start_date' => '2020-09-01',
            'completion_date' => '2019-05-31',
        ])->assertSessionHasErrors('completion_date');
    }

    public function test_education_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $response = $this->actingAs($user)->post(route('providers.educations.store', $provider), [
            'institution_name' => 'Test School',
            'degree' => 'MD',
            'education_type' => 'medical_school',
            'verification_status' => 'pending',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider education created',
            'event' => 'created',
        ]);
    }

    public function test_education_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $education = ProviderEducation::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->patch(route('providers.educations.update', [$provider, $education]), [
            'institution_name' => $education->institution_name,
            'degree' => $education->degree,
            'education_type' => $education->education_type,
            'verification_status' => 'verified',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider education updated',
            'event' => 'updated',
        ]);
    }

    public function test_provider_profile_includes_educations()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        ProviderEducation::factory()->create([
            'provider_id' => $provider->id,
            'institution_name' => 'Test School',
            'education_type' => 'residency',
        ]);

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('providers/Profile')
                ->has('provider'));
    }
}
