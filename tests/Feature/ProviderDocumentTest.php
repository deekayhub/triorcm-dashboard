<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderDocument;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_add_document()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.documents.store', $provider), [
            'type' => 'license',
            'name' => 'Medical License',
            'issue_date' => '2020-01-01',
            'expiration_date' => '2027-01-01',
            'file' => UploadedFile::fake()->create('license.pdf', 100),
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('provider_documents', [
            'provider_id' => $provider->id,
            'type' => 'license',
            'name' => 'Medical License',
            'status' => 'pending',
            'is_current' => true,
        ]);
    }

    public function test_viewer_cannot_add_document()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.documents.store', $provider), [
            'type' => 'license',
            'name' => 'Medical License',
            'file' => UploadedFile::fake()->create('license.pdf', 100),
        ])->assertForbidden();
    }

    public function test_document_validation_requires_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.documents.store', $provider), [
            'type' => '',
            'name' => '',
            'file' => null,
        ])->assertSessionHasErrors(['type', 'name', 'file']);
    }

    public function test_expiration_date_cannot_be_before_issue_date()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.documents.store', $provider), [
            'type' => 'license',
            'name' => 'Medical License',
            'issue_date' => '2027-01-01',
            'expiration_date' => '2020-01-01',
            'file' => UploadedFile::fake()->create('license.pdf', 100),
        ])->assertSessionHasErrors('expiration_date');
    }

    public function test_super_admin_can_update_document()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $document = ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Original Name',
        ]);

        $this->actingAs($user)->patch(route('providers.documents.update', [$provider, $document]), [
            'name' => 'Updated Name',
            'type' => 'certification',
        ])->assertSessionHas('success');

        $this->assertEquals('Updated Name', $document->fresh()->name);
        $this->assertEquals('certification', $document->fresh()->type);
    }

    public function test_super_admin_can_archive_document()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $document = ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->post(route('providers.documents.archive', [$provider, $document]))
            ->assertSessionHas('success');

        $this->assertNotNull($document->fresh()->archived_at);
        $this->assertFalse($document->fresh()->is_current);
    }

    public function test_viewer_cannot_archive_document()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $document = ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->post(route('providers.documents.archive', [$provider, $document]))
            ->assertForbidden();

        $this->assertNull($document->fresh()->archived_at);
    }

    public function test_document_activity_logged_on_create()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();

        $this->actingAs($user)->post(route('providers.documents.store', $provider), [
            'type' => 'license',
            'name' => 'Medical License',
            'file' => UploadedFile::fake()->create('license.pdf', 100),
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider document uploaded',
            'event' => 'uploaded',
        ]);
    }

    public function test_document_activity_logged_on_update()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $document = ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Original Name',
        ]);

        $this->actingAs($user)->patch(route('providers.documents.update', [$provider, $document]), [
            'name' => 'Updated Name',
            'type' => 'certification',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Provider document updated',
            'event' => 'updated',
        ]);
    }

    public function test_document_cannot_be_accessed_through_another_provider()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $otherProvider = Provider::factory()->create();

        $document = ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Original Name',
        ]);

        $this->actingAs($user)->patch(route('providers.documents.update', [$otherProvider, $document]), [
            'type' => 'license',
            'name' => 'Hijacked Name',
        ])->assertNotFound();

        $this->actingAs($user)->post(route('providers.documents.archive', [$otherProvider, $document]))
            ->assertNotFound();

        $this->assertNull($document->fresh()->archived_at);
        $this->assertEquals('Original Name', $document->fresh()->name);
    }

    public function test_super_admin_can_replace_document_version()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $document = ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
            'version' => 1,
            'is_current' => true,
        ]);

        $this->actingAs($user)->post(route('providers.documents.replace', [$provider, $document]), [
            'name' => 'New Version Name',
            'file' => UploadedFile::fake()->create('license-v2.pdf', 100),
        ])->assertSessionHas('success');

        $this->assertFalse($document->fresh()->is_current);
        $this->assertDatabaseHas('provider_documents', [
            'version_group' => $document->fresh()->version_group,
            'is_current' => true,
            'version' => 2,
            'status' => 'pending',
        ]);
    }

    public function test_user_with_sensitive_permission_can_review_document()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $document = ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)->patch(route('providers.documents.review', [$provider, $document]), [
            'status' => 'approved',
        ])->assertSessionHas('success');

        $this->assertEquals('approved', $document->fresh()->status);
        $this->assertNotNull($document->fresh()->reviewed_by);
        $this->assertNotNull($document->fresh()->reviewed_at);
    }

    public function test_rejecting_document_requires_review_notes()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $document = ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)->patch(route('providers.documents.review', [$provider, $document]), [
            'status' => 'rejected',
        ])->assertSessionHasErrors('review_notes');

        $this->assertEquals('pending', $document->fresh()->status);
    }

    public function test_viewer_cannot_review_document()
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');

        $provider = Provider::factory()->create();
        $document = ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->patch(route('providers.documents.review', [$provider, $document]), [
            'status' => 'approved',
        ])->assertForbidden();
    }

    public function test_unsigned_download_is_rejected()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        $document = ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $this->actingAs($user)->get(route('providers.documents.download', [$provider, $document]))
            ->assertForbidden();
    }

    public function test_provider_profile_includes_documents()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $provider = Provider::factory()->create();
        ProviderDocument::factory()->create([
            'provider_id' => $provider->id,
            'is_current' => true,
        ]);

        $this->actingAs($user)
            ->get(route('providers.profile', $provider))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('providers/Profile')
                ->has('provider'));
    }
}
