<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleAdsCampaignDailyMetric extends Model
{
    protected $fillable = [
        'google_ads_campaign_id',
        'date',
        'impressions',
        'clicks',
        'cost_micros',
        'conversions',
        'all_conversions',
        'conversion_value',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'impressions' => 'integer',
            'clicks' => 'integer',
            'cost_micros' => 'integer',
            'conversions' => 'decimal:4',
            'all_conversions' => 'decimal:4',
            'conversion_value' => 'decimal:2',
            'synced_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            GoogleAdsCampaign::class
        );
    }
}