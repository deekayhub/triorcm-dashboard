<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'created_by',
    ];

    protected $dates = ['deleted_at'];

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
