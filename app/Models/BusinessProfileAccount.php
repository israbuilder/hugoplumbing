<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProfileAccount extends Model
{
    protected $fillable = [
        'integration_account_id',
        'account_name',
        'account_id',
        'display_name',
        'account_type',
        'role',
        'verification_state',
        'is_active',
        'metadata',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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

    public function locations(): HasMany
    {
        return $this->hasMany(
            BusinessProfileLocation::class
        );
    }
}