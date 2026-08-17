<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsDailyMetric extends Model
{
    protected $fillable = [
        'analytics_property_id',
        'date',
        'active_users',
        'total_users',
        'new_users',
        'sessions',
        'engaged_sessions',
        'engagement_rate',
        'average_session_duration',
        'screen_page_views',
        'event_count',
        'key_events',
        'total_revenue',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',

            'active_users' => 'integer',
            'total_users' => 'integer',
            'new_users' => 'integer',
            'sessions' => 'integer',
            'engaged_sessions' => 'integer',
            'screen_page_views' => 'integer',
            'event_count' => 'integer',

            'engagement_rate' => 'decimal:8',

            'average_session_duration' =>
                'decimal:4',

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