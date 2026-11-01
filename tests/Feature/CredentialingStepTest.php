<?php

namespace Tests\Feature;

use App\Models\CredentialingStep;
use App\Models\ProviderApplication;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CredentialingStepTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_create_credentialing_step()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $application = ProviderApplication::factory()->create();

        $this->actingAs($user)->post(route('credentialing-steps.store'), [
            'provider_application_id' => $application->id,
            'name' => 'Primary Source Verification',
            'order' => 1,
            'status' => 'pending',
        ])->assertRedirect();

        $this->assertDatabaseHas('credentialing_steps', [
            'provider_application_id' => $application->id,
            'name' => 'Primary Source Verification',
            'order' => 1,
            'status' => 'pending',
        ]);
    }

    public function test_viewer_can_create_credentialing_step()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $application = ProviderApplication::factory()->create();

        $this->actingAs($user)->post(route('credentialing-steps.store'), [
            'provider_application_id' => $application->id,
            'name' => 'Primary Source Verification',
            'order' => 1,
            'status' => 'pending',
        ])->assertRedirect();
    }

    public function test_credentialing_step_requires_required_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $application = ProviderApplication::factory()->create();

        $this->actingAs($user)->post(route('credentialing-steps.store'), [
            'provider_application_id' => $application->id,
            'name' => '',
            'status' => '',
        ])->assertSessionHasErrors(['name', 'status']);
    }

    public function test_credentialing_step_status_must_be_valid_enum()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $application = ProviderApplication::factory()->create();

        $this->actingAs($user)->post(route('credentialing-steps.store'), [
            'provider_application_id' => $application->id,
            'name' => 'Primary Source Verification',
            'order' => 1,
            'status' => 'invalid_status',
        ])->assertSessionHasErrors('status');
    }

    public function test_super_admin_can_update_credentialing_step()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $application = ProviderApplication::factory()->create();
        $step = CredentialingStep::factory()->create([
            'provider_application_id' => $application->id,
            'name' => 'Old Name',
            'status' => 'pending',
        ]);

        $this->actingAs($user)->patch(route('credentialing-steps.update', $step), [
            'provider_application_id' => $application->id,
            'name' => 'New Name',
            'order' => $step->order,
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertEquals('New Name', $step->fresh()->name);
        $this->assertEquals('completed', $step->fresh()->status);
        $this->assertNotNull($step->fresh()->completed_at);
    }

    public function test_super_admin_can_delete_credentialing_step()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $step = CredentialingStep::factory()->create();

        $this->actingAs($user)->delete(route('credentialing-steps.destroy', $step))
            ->assertRedirect();

        $this->assertDatabaseMissing('credentialing_steps', [
            'id' => $step->id,
        ]);
    }

    public function test_super_admin_can_view_credentialing_step()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $step = CredentialingStep::factory()->create();

        $this->actingAs($user)->get(route('credentialing-steps.show', $step))
            ->assertOk();
    }

    public function test_viewer_can_view_credentialing_step()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $step = CredentialingStep::factory()->create();

        $this->actingAs($user)->get(route('credentialing-steps.show', $step))
            ->assertOk();
    }

    public function test_credentialing_step_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $application = ProviderApplication::factory()->create();

        $this->actingAs($user)->post(route('credentialing-steps.store'), [
            'provider_application_id' => $application->id,
            'name' => 'Education Verification',
            'order' => 2,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Credentialing step created',
        ]);
    }

    public function test_credentialing_step_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $application = ProviderApplication::factory()->create();
        $step = CredentialingStep::factory()->create([
            'provider_application_id' => $application->id,
        ]);

        $this->actingAs($user)->patch(route('credentialing-steps.update', $step), [
            'provider_application_id' => $application->id,
            'name' => $step->name,
            'order' => $step->order,
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Credentialing step updated',
        ]);
    }

    public function test_credentialing_step_belongs_to_application()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $application = ProviderApplication::factory()->create();
        $otherApplication = ProviderApplication::factory()->create();

        $step = CredentialingStep::factory()->create([
            'provider_application_id' => $application->id,
        ]);

        $this->actingAs($user)->patch(route('credentialing-steps.update', $step), [
            'provider_application_id' => $otherApplication->id,
            'status' => 'completed',
        ]);

        $this->assertEquals($application->id, $step->fresh()->provider_application_id);
    }
}
