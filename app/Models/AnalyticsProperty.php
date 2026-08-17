<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsProperty extends Model
{
    protected $fillable = [
        'integration_account_id',
        'property_id',
        'property_name',
        'display_name',
        'account_id',
        'account_name',
        'time_zone',
        'currency_code',
        'property_type',
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

    public function dailyMetrics(): HasMany
    {
        return $this->hasMany(
            AnalyticsDailyMetric::class
        );
    }

    public function pageMetrics(): HasMany
    {
        return $this->hasMany(
            AnalyticsPageMetric::class
        );
    }

    public function trafficMetrics(): HasMany
    {
        return $this->hasMany(
            AnalyticsTrafficMetric::class
        );
    }

    public function eventMetrics(): HasMany
    {
        return $this->hasMany(
            AnalyticsEventMetric::class
        );
    }
}