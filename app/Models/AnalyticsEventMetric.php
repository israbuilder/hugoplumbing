<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEventMetric extends Model
{
    protected $fillable = [
        'analytics_property_id',
        'date',
        'event_name',
        'event_count',
        'total_users',
        'key_events',
        'event_value',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',

            'event_count' => 'integer',
            'total_users' => 'integer',

            'key_events' => 'decimal:4',
            'event_value' => 'decimal:4',

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