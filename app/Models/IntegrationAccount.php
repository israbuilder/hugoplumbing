<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IntegrationAccount extends Model
{
    protected $fillable = [
        'integration_id',
        'external_account_id',
        'name',
        'email',
        'status',
        'metadata',
        'connected_at',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'connected_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function token(): HasOne
    {
        return $this->hasOne(IntegrationToken::class);
    }

    public function syncRuns(): HasMany
    {
        return $this->hasMany(SyncRun::class);
    }

    public function searchConsoleSites(): HasMany
    {
        return $this->hasMany(SearchConsoleSite::class);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function analyticsProperties(): HasMany
    {
        return $this->hasMany(
            AnalyticsProperty::class
        );
    }

    public function businessProfileAccounts(): HasMany
    {
        return $this->hasMany(
            BusinessProfileAccount::class
        );
    }

    public function googleAdsCustomers(): HasMany
    {
        return $this->hasMany(
            GoogleAdsCustomer::class
        );
    }
}