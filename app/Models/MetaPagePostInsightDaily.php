<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaPagePostInsightDaily extends Model
{
    protected $fillable = [
        'meta_page_post_id',
        'date',
        'impressions',
        'reach',
        'engaged_users',
        'clicks',
        'reactions',
        'comments',
        'shares',
        'metrics',
        'raw',
    ];

    protected $casts = [
        'date' => 'date',
        'metrics' => 'array',
        'raw' => 'array',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(MetaPagePost::class);
    }
}