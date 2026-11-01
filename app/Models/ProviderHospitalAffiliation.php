<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderHospitalAffiliation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'facility_name',
        'department',
        'affiliation_type',
        'start_date',
        'end_date',
        'status',
        'contact_phone',
        'contact_email',
        'evidence_document_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function evidenceDocument(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ProviderDocument::class, 'evidence_document_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['facility_name', 'department', 'affiliation_type', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider hospital affiliation has been {$eventName}");
    }
}
