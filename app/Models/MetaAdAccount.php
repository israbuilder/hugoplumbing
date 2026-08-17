<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaAdAccount extends Model
{
    protected $fillable = [
        'meta_connection_id',
        'meta_business_account_id',
        'meta_ad_account_id',
        'account_id',
        'name',
        'currency',
        'timezone_name',
        'timezone_offset_hours_utc',
        'account_status',
        'disable_reason',
        'balance',
        'amount_spent',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
        'balance' => 'decimal:2',
        'amount_spent' => 'decimal:2',
    ];

    public function connection(): BelongsTo
    {
        return $this->belongsTo(MetaConnection::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(MetaCampaign::class);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(MetaAdInsightDaily::class);
    }
}