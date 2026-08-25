<?php

namespace App\Models;

use App\Enums\SalesPersonStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Salesperson extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'sales_team_id',
        'employee_number',
        'name',
        'display_name',
        'email',
        'phone',
        'photo_path',
        'avatar_path',
        'avatar_color',
        'avatar_animation',
        'status',
        'show_on_dashboard',
        'sort_order',
        'hire_date',
        'settings',
    ];

    protected $appends = [
        'effective_name',
        'avatar_url',
    ];

    protected function casts(): array
    {
        return [
            'status' => SalesPersonStatus::class,
            'show_on_dashboard' => 'boolean',
            'sort_order' => 'integer',
            'hire_date' => 'date',
            'settings' => 'array',
        ];
    }

    protected function effectiveName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->display_name ?: $this->name,
        );
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->avatar_path) {
                    return null;
                }

                return Storage::disk('public')
                    ->url($this->avatar_path);
            },
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(
            SalesTeam::class,
            'sales_team_id'
        );
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function approvedSales(): HasMany
    {
        return $this->sales()
            ->where('status', 'approved');
    }

    public function goalParticipations(): HasMany
    {
        return $this->hasMany(
            SalesGoalParticipant::class
        );
    }

    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(
            SalesGoal::class,
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

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            'status',
            SalesPersonStatus::Active->value
        );
    }

    public function scopeVisibleOnDashboard(
        Builder $query
    ): Builder {
        return $query->where(
            'show_on_dashboard',
            true
        );
    }

    public function approvedRevenueBetween(
        mixed $startsAt,
        mixed $endsAt
    ): float {
        return (float) $this->sales()
            ->approved()
            ->between($startsAt, $endsAt)
            ->sum('amount');
    }

    public function approvedSalesCountBetween(
        mixed $startsAt,
        mixed $endsAt
    ): int {
        return $this->sales()
            ->approved()
            ->between($startsAt, $endsAt)
            ->count();
    }
}