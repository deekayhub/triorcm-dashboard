<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderReference extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'reference_name',
        'organization',
        'designation',
        'relationship',
        'email',
        'phone',
        'reference_date',
        'verification_status',
        'notes',
    ];

    protected $casts = [
        'reference_date' => 'date',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['reference_name', 'organization', 'designation', 'relationship', 'email', 'phone', 'reference_date', 'verification_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider reference has been {$eventName}");
    }
}
