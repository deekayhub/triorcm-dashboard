<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderEducation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'institution_name',
        'degree',
        'education_type',
        'specialty',
        'start_date',
        'completion_date',
        'country',
        'state',
        'city',
        'verification_status',
        'document_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'completion_date' => 'date',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ProviderDocument::class, 'document_id');
    }

    public function isCompleted(): bool
    {
        return ! is_null($this->completion_date);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['institution_name', 'degree', 'education_type', 'verification_status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider education has been {$eventName}");
    }
}
