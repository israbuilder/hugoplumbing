<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaCampaign extends Model
{
    protected $fillable = [
        'meta_ad_account_id',
        'meta_campaign_id',
        'name',
        'objective',
        'status',
        'effective_status',
        'buying_type',
        'special_ad_category',
        'daily_budget',
        'lifetime_budget',
        'start_time',
        'stop_time',
        'meta_created_time',
        'meta_updated_time',
        'raw',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'stop_time' => 'datetime',
        'meta_created_time' => 'datetime',
        'meta_updated_time' => 'datetime',
        'raw' => 'array',
    ];

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(MetaAdAccount::class);
    }

    public function adSets(): HasMany
    {
        return $this->hasMany(MetaAdSet::class);
    }
}