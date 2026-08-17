<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaConnection extends Model
{
    protected $fillable = [
        'integration_id',
        'meta_user_id',
        'name',
        'access_token',
        'token_expires_at',
        'scopes',
        'is_active',
        'last_synced_at',
        'last_error',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'scopes' => 'array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function adAccounts(): HasMany
    {
        return $this->hasMany(MetaAdAccount::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(MetaPage::class);
    }
}