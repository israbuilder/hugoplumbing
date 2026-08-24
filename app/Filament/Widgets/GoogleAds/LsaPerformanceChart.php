<?php

namespace App\Filament\Widgets\GoogleAds;

use App\Services\Analytics\LsaAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class LsaPerformanceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading =
        'LSA Performance';

    protected ?string $description =
        'Spend, charged leads, phone calls and connected calls.';

    protected int|string|array $columnSpan =
        'full';

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

        $rows =
            $service
                ->performanceTimeline(
                    $customerId,
                    $start,
                    $end
                );

        return [
            'datasets' => [

                [
                    'label' =>
                        'Spend',

                    'data' =>
                        $rows
                            ->pluck('spend')
                            ->map(
                                fn ($value) =>
                                    round(
                                        (float) $value,
                                        2
                                    )
                            )
                            ->values()
                            ->all(),

                    'yAxisID' =>
                        'yMoney',
                ],

                [
                    'label' =>
                        'Charged Leads',

                    'data' =>
                        $rows
                            ->pluck(
                                'charged_leads'
                            )
                            ->map(
                                fn ($value) =>
                                    (int) $value
                            )
                            ->values()
                            ->all(),

                    'yAxisID' =>
                        'yCount',
                ],

                [
                    'label' =>
                        'Phone Calls',

                    'data' =>
                        $rows
                            ->pluck(
                                'phone_calls'
                            )
                            ->map(
                                fn ($value) =>
                                    (int) $value
                            )
                            ->values()
                            ->all(),

                    'yAxisID' =>
                        'yCount',
                ],

                [
                    'label' =>
                        'Connected Calls',

                    'data' =>
                        $rows
                            ->pluck(
                                'connected_phone_calls'
                            )
                            ->map(
                                fn ($value) =>
                                    (int) $value
                            )
                            ->values()
                            ->all(),

                    'yAxisID' =>
                        'yCount',
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

            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],

            'scales' => [

                'yMoney' => [
                    'type' => 'linear',
                    'position' => 'left',
                    'beginAtZero' => true,
                ],

                'yCount' => [
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
}