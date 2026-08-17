<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesTeam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'logo_path',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function salespeople(): HasMany
    {
        return $this->hasMany(Salesperson::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(SalesGoal::class);
    }

    public function dashboardSlides(): HasMany
    {
        return $this->hasMany(DashboardSlide::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}