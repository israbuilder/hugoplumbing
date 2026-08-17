<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleAdsLsaConversation extends Model
{
    protected $fillable = [
        'google_ads_lsa_lead_id',
        'conversation_id',
        'resource_name',
        'channel',
        'participant_type',
        'call_duration_millis',
        'call_recording_url',
        'message_text',
        'attachment_urls',
        'event_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'call_duration_millis' => 'integer',

            'call_recording_url' => 'encrypted',
            'message_text' => 'encrypted',
            'attachment_urls' => 'encrypted:array',

            'event_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(
            GoogleAdsLsaLead::class,
            'google_ads_lsa_lead_id'
        );
    }
}