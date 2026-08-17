<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaInstagramAccount extends Model
{
    protected $fillable = [
        'meta_page_id',
        'meta_instagram_id',
        'username',
        'name',
        'profile_picture_url',
        'followers_count',
        'follows_count',
        'media_count',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(MetaPage::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(MetaInstagramMedia::class);
    }
}