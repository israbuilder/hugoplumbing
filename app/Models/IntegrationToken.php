<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationToken extends Model
{
    protected $fillable = [
        'integration_account_id',
        'access_token',
        'refresh_token',
        'token_type',
        'scopes',
        'expires_at',
        'refreshed_at',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',

            'scopes' => 'array',

            'expires_at' => 'datetime',
            'refreshed_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(
            IntegrationAccount::class,
            'integration_account_id'
        );
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return true;
        }

        return $this->expires_at->isPast();
    }

    public function expiresSoon(int $minutes = 5): bool
    {
        if (!$this->expires_at) {
            return true;
        }

        return now()->addMinutes($minutes)
            ->greaterThanOrEqualTo($this->expires_at);
    }
}