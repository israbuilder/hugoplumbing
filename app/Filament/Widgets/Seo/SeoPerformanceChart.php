<?php

namespace App\Filament\Widgets\Seo;

use App\Models\SearchConsoleSite;
use App\Services\Analytics\SeoAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SeoPerformanceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading =
        'Organic Search Performance';

    protected ?string $description =
        'Daily clicks and impressions from Google Search Console.';

    protected int|string|array $columnSpan =
        'full';

    protected ?string $pollingInterval =
        null;
protected function getData(): array
{
    $siteId = $this->siteId();

    if (!$siteId) {
        return [
            'datasets' => [],
            'labels' => [],
        ];
    }

    $service = app(
        SeoAnalyticsService::class
    );

    [
        $start,
        $end
    ] = $service->resolveDates(
        $this->pageFilters['startDate'] ?? null,
        $this->pageFilters['endDate'] ?? null
    );

    $rows = $service->timeline(
        $siteId,
        $start,
        $end
    );

    return [
        'datasets' => [

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

                'borderColor' => '#2563EB',
                'backgroundColor' => 'rgba(37, 99, 235, 0.15)',

                'pointBackgroundColor' => '#2563EB',
                'pointBorderColor' => '#2563EB',

                'borderWidth' => 2,
                'pointRadius' => 3,
                'pointHoverRadius' => 6,

                'tension' => 0.35,
                'fill' => false,

                'yAxisID' => 'y',
            ],

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

                'borderColor' => '#8B5CF6',
                'backgroundColor' => 'rgba(139, 92, 246, 0.15)',

                'pointBackgroundColor' => '#8B5CF6',
                'pointBorderColor' => '#8B5CF6',

                'borderWidth' => 2,
                'pointRadius' => 3,
                'pointHoverRadius' => 6,

                'tension' => 0.35,
                'fill' => false,

                'yAxisID' => 'y1',
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
    // protected function getData(): array
    // {
    //     $siteId = $this->siteId();

    //     if (!$siteId) {
    //         return [
    //             'datasets' => [],
    //             'labels' => [],
    //         ];
    //     }

    //     $service = app(
    //         SeoAnalyticsService::class
    //     );

    //     [
    //         $start,
    //         $end
    //     ] = $service->resolveDates(
    //         $this->pageFilters['startDate']
    //             ?? null,

    //         $this->pageFilters['endDate']
    //             ?? null
    //     );

    //     $rows =
    //         $service->timeline(
    //             $siteId,
    //             $start,
    //             $end
    //         );

    //     return [
    //         'datasets' => [
    //             [
    //                 'label' => 'Clicks',

    //                 'data' =>
    //                     $rows
    //                         ->pluck('clicks')
    //                         ->map(
    //                             fn ($value) =>
    //                                 (int) $value
    //                         )
    //                         ->values()
    //                         ->all(),

    //                 'yAxisID' => 'y',
    //             ],

    //             [
    //                 'label' =>
    //                     'Impressions',

    //                 'data' =>
    //                     $rows
    //                         ->pluck('impressions')
    //                         ->map(
    //                             fn ($value) =>
    //                                 (int) $value
    //                         )
    //                         ->values()
    //                         ->all(),

    //                 'yAxisID' => 'y1',
    //             ],
    //         ],

    //         'labels' =>
    //             $rows
    //                 ->pluck('date')
    //                 ->map(
    //                     fn ($date) =>
    //                         $date->format('M j')
    //                 )
    //                 ->values()
    //                 ->all(),
    //     ];
    // }

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

            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'position' => 'left',
                    'beginAtZero' => true,
                ],

                'y1' => [
                    'type' => 'linear',
                    'position' => 'right',
                    'beginAtZero' => true,

                    'grid' => [
                        'drawOnChartArea' =>
                            false,
                    ],
                ],
            ],
        ];
    }

    protected function siteId(): ?int
    {
        $siteId =
            $this->pageFilters['siteId']
            ?? null;

        if ($siteId) {
            return (int) $siteId;
        }

        return SearchConsoleSite::query()
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->value('id');
    }
}