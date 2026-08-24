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
        $service =
            app(
                LsaAnalyticsService::class
            );

        $customerId =
            isset(
                $this->pageFilters[
                    'customerId'
                ]
            )
                ? (int)
                    $this->pageFilters[
                        'customerId'
                    ]
                : $service
                    ->defaultCustomerId();

        if (!$customerId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        [
            $start,
            $end
        ] =
            $service
                ->resolveDates(
                    $this
                        ->pageFilters[
                            'startDate'
                        ]
                        ?? null,

                    $this
                        ->pageFilters[
                            'endDate'
                        ]
                        ?? null
                );

        $campaignId =
            isset(
                $this->pageFilters[
                    'campaignId'
                ]
            )
                ? (int)
                    $this->pageFilters[
                        'campaignId'
                    ]
                : null;

        $rows =
            $service
                ->campaignTimeline(
                    $customerId,
                    $start,
                    $end,
                    $campaignId
                );

        return [
            'datasets' => [

                [
                    'label' =>
                        'Impressions',

                    'data' =>
                        $rows
                            ->pluck(
                                'impressions'
                            )
                            ->map(
                                fn ($value) =>
                                    (int) $value
                            )
                            ->values()
                            ->all(),

                    'yAxisID' =>
                        'yImpressions',
                ],

                [
                    'label' =>
                        'Clicks',

                    'data' =>
                        $rows
                            ->pluck(
                                'clicks'
                            )
                            ->map(
                                fn ($value) =>
                                    (int) $value
                            )
                            ->values()
                            ->all(),

                    'yAxisID' =>
                        'yActions',
                ],

                [
                    'label' =>
                        'Conversions',

                    'data' =>
                        $rows
                            ->pluck(
                                'conversions'
                            )
                            ->map(
                                fn ($value) =>
                                    (float) $value
                            )
                            ->values()
                            ->all(),

                    'yAxisID' =>
                        'yActions',
                ],
            ],

            'labels' =>
                $rows
                    ->pluck('date')
                    ->map(
                        fn ($date) =>
                            $date->format(
                                'M j'
                            )
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

            'scales' => [

                'yImpressions' => [
                    'beginAtZero' =>
                        true,

                    'position' =>
                        'left',
                ],

                'yActions' => [
                    'beginAtZero' =>
                        true,

                    'position' =>
                        'right',

                    'grid' => [
                        'drawOnChartArea' =>
                            false,
                    ],
                ],
            ],
        ];
    }
}