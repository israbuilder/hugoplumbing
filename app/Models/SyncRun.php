<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncRun extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'integration_account_id',
        'type',
        'status',
        'date_from',
        'date_to',
        'rows_processed',
        'rows_created',
        'rows_updated',
        'error_message',
        'metadata',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',

            'metadata' => 'array',

            'started_at' => 'datetime',
            'finished_at' => 'datetime',

            'rows_processed' => 'integer',
            'rows_created' => 'integer',
            'rows_updated' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(
            IntegrationAccount::class,
            'integration_account_id'
        );
    }

    public function markRunning(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    public function markSuccess(): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESS,
            'finished_at' => now(),
        ]);
    }

    public function markFailed(\Throwable|string $error): void
    {
        $message = $error instanceof \Throwable
            ? $error->getMessage()
            : $error;

        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $message,
            'finished_at' => now(),
        ]);
    }
}