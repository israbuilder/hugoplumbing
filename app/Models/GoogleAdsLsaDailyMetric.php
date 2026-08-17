<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleAdsLsaDailyMetric extends Model
{
    protected $fillable = [
        'google_ads_customer_id',
        'date',
        'average_weekly_budget',
        'rating',
        'review_count',
        'impressions_last_two_days',
        'phone_lead_responsiveness',
        'charged_leads',
        'total_cost',
        'currency_code',
        'phone_calls',
        'connected_phone_calls',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'average_weekly_budget' => 'decimal:2',
            'rating' => 'decimal:2',
            'review_count' => 'integer',
            'impressions_last_two_days' => 'integer',
            'phone_lead_responsiveness' => 'decimal:6',
            'charged_leads' => 'integer',
            'total_cost' => 'decimal:2',
            'phone_calls' => 'integer',
            'connected_phone_calls' => 'integer',
            'synced_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            GoogleAdsCustomer::class
        );
    }
}