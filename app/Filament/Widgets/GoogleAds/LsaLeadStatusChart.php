<?php

namespace App\Filament\Widgets\GoogleAds;

use App\Services\Analytics\LsaAnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class LsaLeadStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading =
        'Lead Status';

    protected ?string $description =
        'Current status of Local Services leads.';

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

    $rows = $service
        ->leadStatuses(
            $customerId,
            $start,
            $end
        );

    $colors = [
    'BOOKED' => '#10B981',             // Green
    'ACTIVE' => '#F59E0B',             // Amber
    'DECLINED' => '#EF4444',           // Red
    'NEW' => '#2563EB',                // Blue
    'CONSUMER_DECLINED' => '#8B5CF6', // Purple
];

    $backgroundColors = $rows
        ->pluck('status')
        ->map(
            fn ($status) =>
                $colors[strtoupper($status)] ?? '#64748B'
        )
        ->values()
        ->all();

    return [
        'datasets' => [
            [
                'label' => 'Leads',

                'data' => $rows
                    ->pluck('total')
                    ->map(
                        fn ($value) => (int) $value
                    )
                    ->values()
                    ->all(),

                'backgroundColor' => $backgroundColors,

                'borderColor' => '#ffffff',

                'borderWidth' => 2,

                'hoverOffset' => 8,
            ],
        ],

        'labels' => $rows
            ->pluck('status')
            ->map(
                fn ($status) =>
                    ucwords(
                        strtolower(
                            str_replace(
                                '_',
                                ' ',
                                $status
                            )
                        )
                    )
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
}