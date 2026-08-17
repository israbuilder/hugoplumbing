<?php

namespace App\Filament\Widgets\Analytics;

use App\Services\Analytics\GoogleAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class AnalyticsTrafficChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading =
        'Website Traffic';

    protected ?string $description =
        'Daily sessions and users.';

    protected int|string|array $columnSpan =
        'full';

    protected ?string $pollingInterval =
        null;

    protected function getData(): array
    {
        $service =
            app(
                GoogleAnalyticsService::class
            );

        $propertyId =
            $this->propertyId(
                $service
            );

        if (!$propertyId) {

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
                ->timeline(
                    $propertyId,
                    $start,
                    $end
                );

        return [
            'datasets' => [

                [
                    'label' =>
                        'Sessions',

                    'data' =>
                        $rows
                            ->pluck(
                                'sessions'
                            )
                            ->map(
                                fn ($value) =>
                                    (int) $value
                            )
                            ->values()
                            ->all(),
                ],

                [
                    'label' =>
                        'Users',

                    'data' =>
                        $rows
                            ->pluck(
                                'active_users'
                            )
                            ->map(
                                fn ($value) =>
                                    (int) $value
                            )
                            ->values()
                            ->all(),
                ],

                [
                    'label' =>
                        'New Users',

                    'data' =>
                        $rows
                            ->pluck(
                                'new_users'
                            )
                            ->map(
                                fn ($value) =>
                                    (int) $value
                            )
                            ->values()
                            ->all(),
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

    protected function propertyId(
        GoogleAnalyticsService $service
    ): ?int {
        return isset(
            $this->pageFilters[
                'propertyId'
            ]
        )
            ? (int)
                $this->pageFilters[
                    'propertyId'
                ]

            : $service
                ->defaultPropertyId();
    }
}