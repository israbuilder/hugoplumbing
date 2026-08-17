<?php

namespace App\Models;

use App\Enums\DashboardSlideType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardSlide extends Model
{
    protected $fillable = [
        'dashboard_id',
        'sales_goal_id',
        'sales_team_id',
        'name',
        'type',
        'title',
        'subtitle',
        'duration_seconds',
        'sort_order',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'type' => DashboardSlideType::class,
            'duration_seconds' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(
            SalesGoal::class,
            'sales_goal_id'
        );
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(
            SalesTeam::class,
            'sales_team_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function effectiveDuration(): int
    {
        return $this->duration_seconds
            ?: $this->dashboard->default_slide_duration;
    }
}