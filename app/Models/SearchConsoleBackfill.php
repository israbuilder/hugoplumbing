<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchConsoleBackfill extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'search_console_site_id',
        'date_from',
        'date_to',
        'status',
        'total_chunks',
        'completed_chunks',
        'failed_chunks',
        'rows_processed',
        'error_message',
        'started_at',
        'finished_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'date_from' => 'date',
            'date_to' => 'date',

            'total_chunks' => 'integer',
            'completed_chunks' => 'integer',
            'failed_chunks' => 'integer',

            'rows_processed' => 'integer',

            'started_at' => 'datetime',
            'finished_at' => 'datetime',

            'metadata' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(
            SearchConsoleSite::class,
            'search_console_site_id'
        );
    }

    public function progress(): float
    {
        if ($this->total_chunks === 0) {
            return 0;
        }

        return min(
            100,
            (
                $this->completed_chunks
                + $this->failed_chunks
            )
            / $this->total_chunks
            * 100
        );
    }

    public function isFinished(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_COMPLETED,
                self::STATUS_PARTIAL,
                self::STATUS_FAILED,
            ],
            true
        );
    }
}