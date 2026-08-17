<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SalesGoalParticipant extends Pivot
{
    protected $table = 'sales_goal_participants';

    public $incrementing = true;

    protected $fillable = [
        'sales_goal_id',
        'salesperson_id',
        'target_value',
        'starting_value',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'starting_value' => 'decimal:2',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(
            SalesGoal::class,
            'sales_goal_id'
        );
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }

    public function effectiveTarget(): float
    {
        return (float) (
            $this->target_value
            ?? $this->goal->target_value
        );
    }
}