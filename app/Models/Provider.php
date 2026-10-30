<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Provider extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'npi',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'fax',
        'dob',
        'ssn_last4',
        'gender',
        'provider_type',
        'specialty',
        'taxonomy_code',
        'user_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ProviderApplication::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(ProviderLicense::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProviderDocument::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(ProviderVerification::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'status', 'npi'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider has been {$eventName}");
    }
}
