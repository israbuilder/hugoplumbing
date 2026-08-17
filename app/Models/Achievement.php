<?php

namespace App\Models;

use App\Enums\AchievementType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
    protected $fillable = [
        'salesperson_id',
        'sales_goal_id',
        'sale_id',
        'type',
        'title',
        'description',
        'value',
        'icon',
        'achieved_at',
        'deduplication_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => AchievementType::class,
            'value' => 'decimal:2',
            'achieved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(
            SalesGoal::class,
            'sales_goal_id'
        );
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function scopeRecent(
        Builder $query,
        int $days = 30
    ): Builder {
        return $query->where(
            'achieved_at',
            '>=',
            now()->subDays($days)
        );
    }
}