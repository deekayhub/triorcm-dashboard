<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $provider_id
 * @property mixed $provider_application_id
 * @property mixed $type
 * @property mixed $name
 * @property mixed $file_name
 * @property mixed $mime_type
 * @property mixed $file_size
 * @property mixed $human_file_size
 * @property mixed $checksum
 * @property mixed $issue_date
 * @property mixed $expiration_date
 * @property mixed $version
 * @property mixed $version_group
 * @property mixed $is_current
 * @property mixed $supersedes_id
 * @property mixed $status
 * @property mixed $reviewed_at
 * @property mixed $review_notes
 * @property mixed $archived_at
 * @property mixed $is_expired
 * @property mixed $is_expiring_soon
 * @property mixed $file_exists
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class ProviderDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'provider_application_id' => $this->provider_application_id,
            'provider' => $this->whenLoaded('provider', fn () => $this->provider ? [
                'id' => $this->provider->id,
                'full_name' => $this->provider->full_name,
                'first_name' => $this->provider->first_name,
                'last_name' => $this->provider->last_name,
                'email' => $this->provider->email,
                'npi' => $this->provider->npi,
            ] : null),
            'application' => $this->whenLoaded('application', fn () => $this->application ? [
                'id' => $this->application->id,
                'application_number' => $this->application->application_number,
                'type' => $this->application->type,
                'status' => $this->application->status,
            ] : null),
            'type' => $this->type,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'human_file_size' => $this->human_file_size,
            'file_exists' => $this->file_exists,
            'checksum' => $this->checksum,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'expiration_date' => $this->expiration_date?->format('Y-m-d'),
            'is_expired' => $this->is_expired,
            'is_expiring_soon' => $this->is_expiring_soon,
            'version' => $this->version,
            'version_group' => $this->version_group,
            'is_current' => $this->is_current,
            'supersedes_id' => $this->supersedes_id,
            'status' => $this->status,
            'reviewer' => $this->whenLoaded('reviewedBy', fn () => $this->reviewedBy ? [
                'id' => $this->reviewedBy->id,
                'name' => $this->reviewedBy->name,
            ] : null),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'review_notes' => $this->review_notes,
            'uploader' => $this->whenLoaded('uploadedBy', fn () => $this->uploadedBy ? [
                'id' => $this->uploadedBy->id,
                'name' => $this->uploadedBy->name,
            ] : null),
            'archiver' => $this->whenLoaded('archivedBy', fn () => $this->archivedBy ? [
                'id' => $this->archivedBy->id,
                'name' => $this->archivedBy->name,
            ] : null),
            'archived_at' => $this->archived_at?->toIso8601String(),
            'versions' => $this->whenLoaded('versions', fn () => $this->versions->map(fn ($version) => [
                'id' => $version->id,
                'version' => $version->version,
                'file_name' => $version->file_name,
                'human_file_size' => $version->human_file_size,
                'status' => $version->status,
                'is_current' => $version->is_current,
                'expiration_date' => $version->expiration_date?->format('Y-m-d'),
                'uploaded_at' => $version->created_at?->toIso8601String(),
                'download_url' => $version->downloadUrl(),
            ])),
            'download_url' => $this->downloadUrl(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
