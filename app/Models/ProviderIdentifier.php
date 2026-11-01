<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderIdentifier extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'identifier_type',
        'identifier_value',
        'issuing_state',
        'issue_date',
        'expiration_date',
        'verification_status',
        'verification_notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiration_date' => 'date',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function isExpired(): bool
    {
        return ! is_null($this->expiration_date) && $this->expiration_date < now();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['identifier_type', 'identifier_value', 'verification_status', 'expiration_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider identifier has been {$eventName}");
    }
}
