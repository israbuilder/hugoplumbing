<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAdInsightDaily extends Model
{
    protected $table = 'meta_ad_insights_daily';

    protected $fillable = [
        'meta_ad_account_id',
        'level',
        'meta_campaign_id',
        'meta_ad_set_id',
        'meta_ad_id',
        'date',
        'impressions',
        'reach',
        'clicks',
        'unique_clicks',
        'inline_link_clicks',
        'spend',
        'cpc',
        'cpm',
        'ctr',
        'frequency',
        'actions',
        'action_values',
        'cost_per_action_type',
        'outbound_clicks',
        'outbound_clicks_ctr',
        'quality_ranking',
        'engagement_rate_ranking',
        'conversion_rate_ranking',
        'raw',
    ];

    protected $casts = [
        'date' => 'date',
        'actions' => 'array',
        'action_values' => 'array',
        'cost_per_action_type' => 'array',
        'outbound_clicks' => 'array',
        'outbound_clicks_ctr' => 'array',
        'raw' => 'array',
    ];

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(MetaAdAccount::class);
    }
}