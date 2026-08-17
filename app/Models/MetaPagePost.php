<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaPagePost extends Model
{
    protected $fillable = [
        'meta_page_id',
        'meta_post_id',
        'message',
        'permalink_url',
        'link',
        'post_type',
        'shares',
        'published_at',
        'raw',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'raw' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(MetaPage::class);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(
            MetaPagePostInsightDaily::class);
    }
}