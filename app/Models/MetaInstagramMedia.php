<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaInstagramMedia extends Model
{
    protected $table = 'meta_instagram_media';

    protected $fillable = [
        'meta_instagram_account_id',
        'meta_media_id',
        'media_type',
        'media_product_type',
        'caption',
        'permalink',
        'media_url',
        'thumbnail_url',
        'published_at',
        'raw',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'raw' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(MetaInstagramAccount::class);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(
            MetaInstagramMediaInsightDaily::class);
    }
}