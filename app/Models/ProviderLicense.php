<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'license_number',
        'state',
        'license_type',
        'issue_date',
        'expiration_date',
        'status',
        'issuing_board',
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
        return $this->expiration_date < now();
    }
}
