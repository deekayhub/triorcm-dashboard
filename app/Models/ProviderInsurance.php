<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderInsurance extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'carrier',
        'policy_number',
        'coverage_type',
        'per_claim_amount',
        'aggregate_amount',
        'effective_date',
        'expiration_date',
        'agent_contact',
        'agent_phone',
        'document_id',
        'verification_status',
    ];

    protected $casts = [
        'per_claim_amount' => 'decimal:2',
        'aggregate_amount' => 'decimal:2',
        'effective_date' => 'date',
        'expiration_date' => 'date',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ProviderDocument::class, 'document_id');
    }

    public function isExpired(): bool
    {
        return $this->expiration_date < now();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['carrier', 'policy_number', 'coverage_type', 'per_claim_amount', 'aggregate_amount', 'expiration_date', 'verification_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider insurance has been {$eventName}");
    }
}
