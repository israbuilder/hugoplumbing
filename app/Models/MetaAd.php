<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAd extends Model
{
    protected $fillable = [
        'meta_ad_set_id',
        'meta_ad_id',
        'name',
        'status',
        'effective_status',
        'creative_id',
        'creative',
        'tracking_specs',
        'conversion_specs',
        'raw',
    ];

    protected $casts = [
        'creative' => 'array',
        'tracking_specs' => 'array',
        'conversion_specs' => 'array',
        'raw' => 'array',
    ];

    public function adSet(): BelongsTo
    {
        return $this->belongsTo(MetaAdSet::class);
    }
}