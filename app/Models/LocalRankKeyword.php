<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocalRankKeyword extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'default_grid_size' => 'integer',
        'default_radius_miles' => 'float',
        'zoom' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(
            LocalRankLocation::class,
            'local_rank_location_id'
        );
    }

    public function scans(): HasMany
    {
        return $this->hasMany(
            LocalRankScan::class,
            'local_rank_keyword_id'
        );
    }
}