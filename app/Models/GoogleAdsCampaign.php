<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleAdsCampaign extends Model
{
    protected $fillable = [
        'google_ads_customer_id',
        'campaign_id',
        'resource_name',
        'name',
        'status',
        'advertising_channel_type',
        'bidding_strategy_type',
        'budget_resource_name',
        'budget_id',
        'budget_amount_micros',
        'budget_period',
        'is_local_services',
        'is_active',
        'local_services_settings',
        'metadata',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'budget_amount_micros' => 'integer',
            'is_local_services' => 'boolean',
            'is_active' => 'boolean',
            'local_services_settings' => 'array',
            'metadata' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            GoogleAdsCustomer::class,
            'google_ads_customer_id'
        );
    }

    public function dailyMetrics(): HasMany
    {
        return $this->hasMany(
            GoogleAdsCampaignDailyMetric::class
        );
    }
}