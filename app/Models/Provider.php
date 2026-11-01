<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Provider extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'display_name',
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
        'team_id',
        'created_by',
        'updated_by',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function personalDetail(): HasOne
    {
        return $this->hasOne(ProviderPersonalDetail::class);
    }

    public function identifiers(): HasMany
    {
        return $this->hasMany(ProviderIdentifier::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(ProviderEducation::class);
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'provider_specialties')
            ->withPivot(['is_primary', 'subspecialty', 'is_board_certified', 'board_certification_date', 'is_active'])
            ->withTimestamps();
    }

    public function providerSpecialties(): HasMany
    {
        return $this->hasMany(ProviderSpecialty::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ProviderLocation::class);
    }

    public function hospitalAffiliations(): HasMany
    {
        return $this->hasMany(ProviderHospitalAffiliation::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ProviderContact::class);
    }

    public function insurances(): HasMany
    {
        return $this->hasMany(ProviderInsurance::class);
    }

    public function employments(): HasMany
    {
        return $this->hasMany(ProviderEmployment::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(ProviderReference::class);
    }

    public function disclosures(): HasMany
    {
        return $this->hasMany(ProviderDisclosure::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'status', 'npi', 'team_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Provider has been {$eventName}");
    }
}
