<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProfileLocation extends Model
{
    protected $fillable = [
        'business_profile_account_id',
        'location_name',
        'location_id',
        'title',
        'store_code',
        'phone',
        'website_uri',
        'primary_category',
        'address_line_1',
        'address_line_2',
        'city',
        'region',
        'postal_code',
        'country_code',
        'latitude',
        'longitude',
        'is_active',
        'is_primary',
        'metadata',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'metadata' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function businessProfileAccount(): BelongsTo
    {
        return $this->belongsTo(
            BusinessProfileAccount::class
        );
    }

    public function dailyMetrics(): HasMany
    {
        return $this->hasMany(
            BusinessProfileDailyMetric::class
        );
    }

    public function searchKeywords(): HasMany
    {
        return $this->hasMany(
            BusinessProfileSearchKeyword::class
        );
    }

    public function integrationAccount(): IntegrationAccount
    {
        return $this
            ->businessProfileAccount
            ->integrationAccount;
    }
}