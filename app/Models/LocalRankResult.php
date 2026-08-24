<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalRankResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'found' => 'boolean',
        'rank' => 'integer',
        'rating' => 'float',
        'reviews_count' => 'integer',
        'items' => 'array',
        'raw_response' => 'array',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(
            LocalRankScan::class,
            'local_rank_scan_id'
        );
    }

    public function point(): BelongsTo
    {
        return $this->belongsTo(
            LocalRankGridPoint::class,
            'local_rank_grid_point_id'
        );
    }
}