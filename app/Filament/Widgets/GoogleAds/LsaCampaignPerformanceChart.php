<?php

namespace App\Filament\Widgets\GoogleAds;

use App\Services\Analytics\LsaAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class LsaCampaignPerformanceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading =
        'Google Ads Campaign Performance';

    protected ?string $description =
        'Impressions, clicks and reported conversions from Google Ads API.';

    protected int|string|array $columnSpan =
        1;

    protected ?string $pollingInterval =
        null;

    protected function getData(): array
{
    $service = app(
        LsaAnalyticsService::class
    );

    $customerId = isset(
        $this->pageFilters['customerId']
    )
        ? (int) $this->pageFilters['customerId']
        : $service->defaultCustomerId();

    if (!$customerId) {
        return [
            'datasets' => [],
            'labels' => [],
        ];
    }

    [$start, $end] = $service
        ->resolveDates(
            $this->pageFilters['startDate'] ?? null,
            $this->pageFilters['endDate'] ?? null
        );

    $campaignId = isset(
        $this->pageFilters['campaignId']
    )
        ? (int) $this->pageFilters['campaignId']
        : null;

    $rows = $service
        ->campaignTimeline(
            $customerId,
            $start,
            $end,
            $campaignId
        );

    return [
        'datasets' => [

            /*
            |--------------------------------------------------------------------------
            | Impressions
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Impressions',

                'data' => $rows
                    ->pluck('impressions')
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

                'yAxisID' => 'yImpressions',
            ],

            /*
            |--------------------------------------------------------------------------
            | Clicks
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Clicks',

                'data' => $rows
                    ->pluck('clicks')
                    ->map(
                        fn ($value) => (int) $value
                    )
                    ->values()
                    ->all(),

                'borderColor' => '#F59E0B',
                'backgroundColor' => 'rgba(245, 158, 11, 0.15)',

                'pointBackgroundColor' => '#F59E0B',
                'pointBorderColor' => '#F59E0B',

                'borderWidth' => 2,
                'pointRadius' => 3,
                'pointHoverRadius' => 6,

                'tension' => 0.35,
                'fill' => false,

                'yAxisID' => 'yActions',
            ],

            /*
            |--------------------------------------------------------------------------
            | Conversions
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Conversions',

                'data' => $rows
                    ->pluck('conversions')
                    ->map(
                        fn ($value) => (float) $value
                    )
                    ->values()
                    ->all(),

                'borderColor' => '#10B981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.15)',

                'pointBackgroundColor' => '#10B981',
                'pointBorderColor' => '#10B981',

                'borderWidth' => 3,
                'pointRadius' => 4,
                'pointHoverRadius' => 7,

                'tension' => 0.35,
                'fill' => false,

                'yAxisID' => 'yActions',
            ],
        ],

        'labels' => $rows
            ->pluck('date')
            ->map(
                fn ($date) => $date->format('M j')
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
        'responsive' => true,

        'maintainAspectRatio' => false,

        'interaction' => [
            'mode' => 'index',
            'intersect' => false,
        ],

        'plugins' => [
            'legend' => [
                'position' => 'bottom',

                'labels' => [
                    'usePointStyle' => true,
                    'pointStyle' => 'circle',
                ],
            ],

            'tooltip' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ],

        'scales' => [

            'x' => [
                'grid' => [
                    'display' => false,
                ],
            ],

            'yImpressions' => [
                'type' => 'linear',

                'beginAtZero' => true,

                'position' => 'left',

                'ticks' => [
                    'precision' => 0,
                ],

                'title' => [
                    'display' => true,
                    'text' => 'Impressions',
                ],
            ],

            'yActions' => [
                'type' => 'linear',

                'beginAtZero' => true,

                'position' => 'right',

                'ticks' => [
                    'precision' => 0,
                ],

                'title' => [
                    'display' => true,
                    'text' => 'Clicks / Conversions',
                ],

                'grid' => [
                    'drawOnChartArea' => false,
                ],
            ],
        ],
    ];
}
}