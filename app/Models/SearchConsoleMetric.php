<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchConsoleMetric extends Model
{
    public const GRAIN_SITE = 'site';
    public const GRAIN_QUERY = 'query';
    public const GRAIN_PAGE = 'page';
    public const GRAIN_QUERY_PAGE = 'query_page';
    public const GRAIN_COUNTRY = 'country';
    public const GRAIN_DEVICE = 'device';
    public const GRAIN_QUERY_COUNTRY_DEVICE = 'query_country_device';

    protected $fillable = [
        'search_console_site_id',
        'date',
        'grain',
        'search_type',
        'data_state',
        'query',
        'page',
        'country',
        'device',
        'search_appearance',
        'clicks',
        'impressions',
        'ctr',
        'position',
        'dimension_hash',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',

            'clicks' => 'integer',
            'impressions' => 'integer',

            'ctr' => 'decimal:8',
            'position' => 'decimal:4',

            'synced_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(
            SearchConsoleSite::class,
            'search_console_site_id'
        );
    }

    public function scopeForSite(
        Builder $query,
        SearchConsoleSite|int $site
    ): Builder {
        $siteId = $site instanceof SearchConsoleSite
            ? $site->getKey()
            : $site;

        return $query->where(
            'search_console_site_id',
            $siteId
        );
    }

    public function scopeBetween(
        Builder $query,
        string $from,
        string $to
    ): Builder {
        return $query->whereBetween('date', [
            $from,
            $to,
        ]);
    }

    public function scopeGrain(
        Builder $query,
        string $grain
    ): Builder {
        return $query->where('grain', $grain);
    }

    public static function makeDimensionHash(
        int $siteId,
        string $date,
        string $grain,
        string $searchType = 'web',
        ?string $query = null,
        ?string $page = null,
        ?string $country = null,
        ?string $device = null,
        ?string $searchAppearance = null,
    ): string {
        return hash('sha256', implode('|', [
            $siteId,
            $date,
            $grain,
            $searchType,
            $query ?? '',
            $page ?? '',
            $country ?? '',
            $device ?? '',
            $searchAppearance ?? '',
        ]));
    }
}