<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaPage extends Model
{
    protected $fillable = [
        'meta_connection_id',
        'meta_page_id',
        'name',
        'category',
        'username',
        'page_access_token',
        'instagram_business_account_id',
        'tasks',
        'raw',
    ];

    protected $casts = [
        'page_access_token' => 'encrypted',
        'tasks' => 'array',
        'raw' => 'array',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(MetaConnection::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(MetaPagePost::class);
    }

    public function instagramAccounts(): HasMany
    {
        return $this->hasMany(MetaInstagramAccount::class);
    }
}