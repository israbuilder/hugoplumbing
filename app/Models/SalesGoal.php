<?php

namespace App\Models;

use App\Enums\GoalPeriod;
use App\Enums\GoalType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesGoal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sales_team_id',
        'name',
        'description',
        'goal_type',
        'period',
        'target_value',
        'currency',
        'starts_at',
        'ends_at',
        'is_active',
        'show_on_dashboard',
        'is_primary',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'goal_type' => GoalType::class,
            'period' => GoalPeriod::class,
            'target_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'show_on_dashboard' => 'boolean',
            'is_primary' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(
            SalesTeam::class,
            'sales_team_id'
        );
    }

    public function participants(): HasMany
    {
        return $this->hasMany(
            SalesGoalParticipant::class
        );
    }

    public function salespeople(): BelongsToMany
    {
        return $this->belongsToMany(
            Salesperson::class,
            'sales_goal_participants'
        )
            ->using(SalesGoalParticipant::class)
            ->withPivot([
                'id',
                'target_value',
                'starting_value',
                'is_active',
                'settings',
            ])
            ->withTimestamps();
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function slides(): HasMany
    {
        return $this->hasMany(DashboardSlide::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVisibleOnDashboard(
        Builder $query
    ): Builder {
        return $query->where(
            'show_on_dashboard',
            true
        );
    }

    public function scopeCurrent(
        Builder $query,
        ?CarbonInterface $date = null
    ): Builder {
        $date ??= now();

        return $query
            ->where('starts_at', '<=', $date)
            ->where('ends_at', '>=', $date);
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    public function targetFor(
        Salesperson $salesperson
    ): float {
        $participant = $this->participants()
            ->where('salesperson_id', $salesperson->id)
            ->where('is_active', true)
            ->first();

        return (float) (
            $participant?->target_value
            ?? $this->target_value
        );
    }
}