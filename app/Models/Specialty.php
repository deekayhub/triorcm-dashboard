<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Specialty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function providers()
    {
        return $this->belongsToMany(\App\Models\Provider::class, 'provider_specialties')
            ->withPivot(['is_primary', 'subspecialty', 'is_board_certified', 'board_certification_date', 'is_active'])
            ->withTimestamps();
    }
}
