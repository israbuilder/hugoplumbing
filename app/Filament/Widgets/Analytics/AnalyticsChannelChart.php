<?php

namespace App\Filament\Widgets\Analytics;

use App\Services\Analytics\GoogleAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class AnalyticsChannelChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading =
        'Traffic by Channel';

    protected ?string $description =
        'Sessions by GA4 default channel group.';

    protected int|string|array $columnSpan =
        1;

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

        $channel =
            $this->pageFilters[
                'channel'
            ]
            ?? null;

        $rows =
            $service
                ->channels(
                    $propertyId,
                    $start,
                    $end,
                    $channel
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
            ],

            'labels' =>
                $rows
                    ->pluck(
                        'channel'
                    )
                    ->values()
                    ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [

            'responsive' =>
                true,

            'maintainAspectRatio' =>
                false,

            'plugins' => [

                'legend' => [
                    'position' =>
                        'bottom',
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