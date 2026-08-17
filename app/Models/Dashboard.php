<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Dashboard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'access_token',
        'timezone',
        'currency',
        'theme',
        'is_active',
        'default_slide_duration',
        'refresh_interval',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'default_slide_duration' => 'integer',
            'refresh_interval' => 'integer',
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Dashboard $dashboard): void {
            if (blank($dashboard->access_token)) {
                $dashboard->access_token = Str::random(64);
            }
        });
    }

    public function slides(): HasMany
    {
        return $this->hasMany(DashboardSlide::class)
            ->orderBy('sort_order');
    }

    public function activeSlides(): HasMany
    {
        return $this->slides()
            ->where('is_active', true);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}