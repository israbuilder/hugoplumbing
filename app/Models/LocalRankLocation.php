<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocalRankLocation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function keywords(): HasMany
    {
        return $this->hasMany(
            LocalRankKeyword::class,
            'local_rank_location_id'
        );
    }

    public function scans(): HasMany
    {
        return $this->hasMany(
            LocalRankScan::class,
            'local_rank_location_id'
        );
    }
}