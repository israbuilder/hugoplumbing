<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocalRankScan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'grid_size' => 'integer',
        'radius_miles' => 'float',
        'zoom' => 'integer',

        'center_latitude' => 'float',
        'center_longitude' => 'float',

        'total_points' => 'integer',
        'completed_points' => 'integer',
        'failed_points' => 'integer',

        'average_rank' => 'float',
        'top_3_percentage' => 'float',
        'top_10_percentage' => 'float',
        'visibility_score' => 'float',

        'provider_cost' => 'float',

        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'coverage_percentage' => 'float',
        'meta' => 'array',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(
            LocalRankLocation::class,
            'local_rank_location_id'
        );
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(
            LocalRankKeyword::class,
            'local_rank_keyword_id'
        );
    }

    public function points(): HasMany
    {
        return $this->hasMany(
            LocalRankGridPoint::class,
            'local_rank_scan_id'
        );
    }

    public function results(): HasMany
    {
        return $this->hasMany(
            LocalRankResult::class,
            'local_rank_scan_id'
        );
    }
}