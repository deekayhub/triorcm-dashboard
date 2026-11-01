<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderDocument extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'provider_id',
        'provider_application_id',
        'type',
        'name',
        'file_path',
        'file_disk',
        'file_name',
        'mime_type',
        'file_size',
        'checksum',
        'issue_date',
        'expiration_date',
        'version',
        'version_group',
        'is_current',
        'supersedes_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'uploaded_by',
        'archived_at',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiration_date' => 'date',
            'reviewed_at' => 'datetime',
            'archived_at' => 'datetime',
            'file_size' => 'integer',
            'version' => 'integer',
            'is_current' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ProviderApplication::class, 'provider_application_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_id');
    }

    public function supersededBy(): HasMany
    {
        return $this->hasMany(self::class, 'supersedes_id');
    }

    /**
     * All versions that share the same version group, oldest first.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'version_group', 'version_group')
            ->whereNotNull('version_group')
            ->orderBy('version');
    }

    /**
     * Limit the query to documents that have not been archived.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Limit the query to the latest version of each document.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true)->whereNull('archived_at');
    }

    /**
     * Limit the query to documents that have passed their expiration date.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', now()->toDateString());
    }

    /**
     * Determine whether the document has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiration_date !== null
            && $this->expiration_date->copy()->endOfDay()->isPast();
    }

    /**
     * Determine whether the document expires within the next 30 days.
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiration_date !== null
            && ! $this->is_expired
            && $this->expiration_date->copy()->startOfDay()->lessThanOrEqualTo(now()->addDays(30)->startOfDay());
    }

    /**
     * Determine whether the stored file is still available on its disk.
     */
    public function getFileExistsAttribute(): bool
    {
        if (blank($this->file_path)) {
            return false;
        }

        return Storage::disk($this->file_disk ?: 'local')->exists($this->file_path);
    }

    /**
     * A temporary signed URL for downloading the document.
     */
    public function downloadUrl(int $minutes = 60): string
    {
        return URL::temporarySignedRoute(
            'providers.documents.download',
            now()->addMinutes($minutes),
            ['provider' => $this->provider_id, 'providerDocument' => $this->id],
        );
    }

    /**
     * Human readable file size.
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes <= 0) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 1).' '.$units[$power];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'type',
                'name',
                'status',
                'issue_date',
                'expiration_date',
                'version',
                'is_current',
                'review_notes',
                'archived_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider document has been {$eventName}");
    }
}
