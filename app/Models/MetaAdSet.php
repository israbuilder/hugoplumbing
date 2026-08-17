<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaAdSet extends Model
{
    protected $fillable = [
        'meta_campaign_id',
        'meta_ad_set_id',
        'name',
        'status',
        'effective_status',
        'optimization_goal',
        'billing_event',
        'bid_amount',
        'daily_budget',
        'lifetime_budget',
        'start_time',
        'end_time',
        'targeting',
        'promoted_object',
        'raw',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'targeting' => 'array',
        'promoted_object' => 'array',
        'raw' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MetaCampaign::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(MetaAd::class);
    }
}