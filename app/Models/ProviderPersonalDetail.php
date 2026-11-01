<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderPersonalDetail extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'alternate_phone',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'languages',
        'previous_names',
    ];

    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'previous_names' => 'array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['address', 'city', 'state', 'zip_code', 'country', 'languages', 'alternate_phone'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider personal details have been {$eventName}");
    }
}
