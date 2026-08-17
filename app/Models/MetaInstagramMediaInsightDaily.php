<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaInstagramMediaInsightDaily extends Model
{
    protected $fillable = [
        'meta_instagram_media_id',
        'date',
        'reach',
        'impressions',
        'likes',
        'comments',
        'shares',
        'saved',
        'total_interactions',
        'plays',
        'metrics',
        'raw',
    ];

    protected $casts = [
        'date' => 'date',
        'metrics' => 'array',
        'raw' => 'array',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(MetaInstagramMedia::class);
    }
}