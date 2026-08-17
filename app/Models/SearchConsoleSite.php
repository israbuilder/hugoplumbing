<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SearchConsoleSite extends Model
{
    protected $fillable = [
        'integration_account_id',
        'site_url',
        'property_type',
        'permission_level',
        'is_active',
        'is_primary',
        'last_synced_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_primary' => 'boolean',

            'last_synced_at' => 'datetime',

            'metadata' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(
            IntegrationAccount::class,
            'integration_account_id'
        );
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(SearchConsoleMetric::class);
    }

    public function backfills(): HasMany
{
    return $this->hasMany(
        SearchConsoleBackfill::class
    );
}
}