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
    $service = app(
        GoogleAnalyticsService::class
    );

    $propertyId = $this->propertyId(
        $service
    );

    if (!$propertyId) {
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

    $rows = $service
        ->timeline(
            $propertyId,
            $start,
            $end
        );

    return [
        'datasets' => [

            /*
            |--------------------------------------------------------------------------
            | Sessions
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Sessions',

                'data' => $rows
                    ->pluck('sessions')
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
            | Users
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'Users',

                'data' => $rows
                    ->pluck('active_users')
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
            | New Users
            |--------------------------------------------------------------------------
            */
            [
                'label' => 'New Users',

                'data' => $rows
                    ->pluck('new_users')
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