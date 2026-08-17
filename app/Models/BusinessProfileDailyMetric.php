<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessProfileDailyMetric extends Model
{
    protected $fillable = [
        'business_profile_location_id',
        'date',
        'metric',
        'value',
        'sub_entity',
        'dimension_hash',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'value' => 'integer',
            'sub_entity' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(
            BusinessProfileLocation::class,
            'business_profile_location_id'
        );
    }
}