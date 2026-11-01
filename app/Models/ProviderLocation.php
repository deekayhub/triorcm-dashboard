<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderLocation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'location_name',
        'practice_name',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'phone',
        'fax',
        'email',
        'office_hours',
        'is_primary',
        'is_billing_address',
        'effective_start_date',
        'effective_end_date',
        'is_active',
    ];

    protected $casts = [
        'office_hours' => 'array',
        'is_primary' => 'boolean',
        'is_billing_address' => 'boolean',
        'is_active' => 'boolean',
        'effective_start_date' => 'date',
        'effective_end_date' => 'date',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['location_name', 'practice_name', 'address', 'city', 'state', 'zip_code', 'is_primary', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider location has been {$eventName}");
    }
}
