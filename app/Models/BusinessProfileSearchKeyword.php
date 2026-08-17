<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessProfileSearchKeyword extends Model
{
    protected $fillable = [
        'business_profile_location_id',
        'month',
        'keyword',
        'impressions',
        'threshold',
        'keyword_hash',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'impressions' => 'integer',
            'threshold' => 'integer',
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