<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleAdsCustomer extends Model
{
    protected $fillable = [
        'integration_account_id',
        'customer_id',
        'descriptive_name',
        'currency_code',
        'time_zone',
        'is_manager',
        'is_test_account',
        'is_active',
        'is_primary',
        'metadata',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_manager' => 'boolean',
            'is_test_account' => 'boolean',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'metadata' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function integrationAccount(): BelongsTo
    {
        return $this->belongsTo(
            IntegrationAccount::class
        );
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(
            GoogleAdsCampaign::class
        );
    }

    public function lsaLeads(): HasMany
    {
        return $this->hasMany(
            GoogleAdsLsaLead::class
        );
    }

    public function lsaDailyMetrics(): HasMany
    {
        return $this->hasMany(
            GoogleAdsLsaDailyMetric::class
        );
    }
}