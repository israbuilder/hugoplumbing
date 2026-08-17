<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleAdsLsaLead extends Model
{
    protected $fillable = [
        'google_ads_customer_id',
        'lead_id',
        'resource_name',
        'lead_type',
        'lead_status',
        'category_id',
        'service_id',
        'locale',
        'contact_phone',
        'consumer_name',
        'phone_extension',
        'lead_charged',
        'credit_state',
        'credit_updated_at',
        'feedback_submitted',
        'note',
        'lead_created_at',
        'last_google_update_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'contact_phone' => 'encrypted',
            'consumer_name' => 'encrypted',
            'note' => 'encrypted',

            'lead_charged' => 'boolean',
            'feedback_submitted' => 'boolean',

            'credit_updated_at' => 'datetime',
            'lead_created_at' => 'datetime',
            'last_google_update_at' => 'datetime',

            'metadata' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            GoogleAdsCustomer::class
        );
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(
            GoogleAdsLsaConversation::class
        );
    }
}