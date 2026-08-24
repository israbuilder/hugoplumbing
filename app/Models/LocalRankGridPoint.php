<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LocalRankGridPoint extends Model
{
    protected $guarded = [];

    protected $casts = [
        'row' => 'integer',
        'column' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'distance_miles' => 'float',
        'is_center' => 'boolean',
        'attempts' => 'integer',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(
            LocalRankScan::class,
            'local_rank_scan_id'
        );
    }

    public function result(): HasOne
    {
        return $this->hasOne(
            LocalRankResult::class,
            'local_rank_grid_point_id'
        );
    }
}