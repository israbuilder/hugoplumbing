<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsTrafficMetric extends Model
{
    protected $fillable = [
        'analytics_property_id',
        'date',
        'source',
        'medium',
        'campaign',
        'channel_group',
        'landing_page',
        'active_users',
        'new_users',
        'sessions',
        'engaged_sessions',
        'engagement_rate',
        'key_events',
        'total_revenue',
        'dimension_hash',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',

            'active_users' => 'integer',
            'new_users' => 'integer',
            'sessions' => 'integer',
            'engaged_sessions' => 'integer',

            'engagement_rate' =>
                'decimal:8',

            'key_events' => 'decimal:4',
            'total_revenue' => 'decimal:2',

            'synced_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(
            AnalyticsProperty::class,
            'analytics_property_id'
        );
    }
}