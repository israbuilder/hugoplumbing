<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsPageMetric extends Model
{
    protected $fillable = [
        'analytics_property_id',
        'date',
        'grain',
        'page_path',
        'page_title',
        'landing_page',
        'active_users',
        'sessions',
        'engaged_sessions',
        'screen_page_views',
        'event_count',
        'key_events',
        'engagement_rate',
        'average_session_duration',
        'dimension_hash',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',

            'active_users' => 'integer',
            'sessions' => 'integer',
            'engaged_sessions' => 'integer',
            'screen_page_views' => 'integer',
            'event_count' => 'integer',

            'key_events' => 'decimal:4',

            'engagement_rate' =>
                'decimal:8',

            'average_session_duration' =>
                'decimal:4',

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