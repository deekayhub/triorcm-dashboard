<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderVerification extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'provider_id',
        'provider_application_id',
        'type',
        'verification_source',
        'status',
        'requested_at',
        'received_at',
        'verified_at',
        'verified_by',
        'notes',
        'findings',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'date',
            'received_at' => 'date',
            'verified_at' => 'date',
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

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'findings'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider verification has been {$eventName}");
    }
}
