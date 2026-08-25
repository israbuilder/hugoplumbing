<?php

namespace App\Filament\Widgets\Seo;

use App\Models\AnalyticsProperty;
use App\Models\SearchConsoleSite;
use App\Services\Analytics\OrganicPerformanceService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OrganicPerformanceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading =
        'Organic Performance';

    protected ?string $description =
        'Search Console clicks compared with GA4 organic sessions and key events.';

    protected int|string|array $columnSpan =
        'full';

    protected ?string $pollingInterval =
        null;

    protected function getData(): array
{
    $service = app(
        OrganicPerformanceService::class
    );

    [
        $siteId,
        $propertyId,
    ] = $this->ids();

    if (
        !$siteId
        || !$propertyId
    ) {
        return [
            'datasets' => [],
            'labels' => [],
        ];
    }

    [
        $start,
        $end
    ] = $service
        ->resolveDates(
            $this->pageFilters['startDate'] ?? null,
            $this->pageFilters['endDate'] ?? null
        );

    $rows = $service
        ->timeline(
            $siteId,
            $propertyId,
            $start,
            $end
        );

    return [
        'datasets' => [

            /*
            |--------------------------------------------------------------------------
            | Search Clicks
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Search Clicks',

                'data' => $rows
                    ->pluck('clicks')
                    ->map(
                        fn ($value) => (int) $value
                    )
                    ->values()
                    ->all(),

                'borderColor' => '#2563EB',
                'backgroundColor' => 'rgba(37, 99, 235, 0.15)',

                'pointBackgroundColor' => '#2563EB',
                'pointBorderColor' => '#2563EB',

                'borderWidth' => 2,
                'pointRadius' => 3,
                'pointHoverRadius' => 6,

                'tension' => 0.35,
                'fill' => false,
            ],

            /*
            |--------------------------------------------------------------------------
            | Organic Sessions
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Organic Sessions',

                'data' => $rows
                    ->pluck('sessions')
                    ->map(
                        fn ($value) => (int) $value
                    )
                    ->values()
                    ->all(),

                'borderColor' => '#10B981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.15)',

                'pointBackgroundColor' => '#10B981',
                'pointBorderColor' => '#10B981',

                'borderWidth' => 2,
                'pointRadius' => 3,
                'pointHoverRadius' => 6,

                'tension' => 0.35,
                'fill' => false,
            ],

            /*
            |--------------------------------------------------------------------------
            | Key Events
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Key Events',

                'data' => $rows
                    ->pluck('key_events')
                    ->map(
                        fn ($value) => (float) $value
                    )
                    ->values()
                    ->all(),

                'borderColor' => '#F59E0B',
                'backgroundColor' => 'rgba(245, 158, 11, 0.15)',

                'pointBackgroundColor' => '#F59E0B',
                'pointBorderColor' => '#F59E0B',

                'borderWidth' => 3,
                'pointRadius' => 4,
                'pointHoverRadius' => 7,

                'tension' => 0.35,
                'fill' => false,
            ],
        ],

        'labels' => $rows
            ->pluck('date')
            ->map(
                fn ($date) =>
                    \Carbon\Carbon::parse($date)
                        ->format('M j')
            )
            ->values()
            ->all(),
    ];
}

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' =>
                true,

            'maintainAspectRatio' =>
                false,

            'interaction' => [
                'mode' =>
                    'index',

                'intersect' =>
                    false,
            ],

            'scales' => [
                'y' => [
                    'beginAtZero' =>
                        true,
                ],
            ],
        ];
    }

    protected function ids(): array
    {
        return [

            (int) (
                $this->pageFilters[
                    'searchConsoleSiteId'
                ]
                ?? SearchConsoleSite::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->orderByDesc(
                        'is_primary'
                    )
                    ->value('id')
            ),

            (int) (
                $this->pageFilters[
                    'analyticsPropertyId'
                ]
                ?? AnalyticsProperty::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->orderByDesc(
                        'is_primary'
                    )
                    ->value('id')
            ),
        ];
    }
}