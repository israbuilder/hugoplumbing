<?php

namespace App\Filament\Widgets\GoogleAds;

use App\Services\Analytics\LsaAnalyticsService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LsaStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan =
        'full';

    protected ?string $pollingInterval =
        null;

    protected function getStats(): array
    {
        $service =
            app(
                LsaAnalyticsService::class
            );

        $customerId =
            $this->customerId(
                $service
            );

        if (!$customerId) {
            return [
                Stat::make(
                    'LSA Spend',
                    'No account'
                ),

                Stat::make(
                    'Charged Leads',
                    'No account'
                ),

                Stat::make(
                    'Cost / Lead',
                    'No account'
                ),

                Stat::make(
                    'Phone Calls',
                    'No account'
                ),

                Stat::make(
                    'Connected Calls',
                    'No account'
                ),

                Stat::make(
                    'Booked Leads',
                    'No account'
                ),
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

        $comparison =
            $service->comparison(
                $customerId,
                $start,
                $end,
                $campaignId
            );

        $current =
            $comparison['current'];

        $changes =
            $comparison['changes'];

        return [

            $this->stat(
                label:
                    'LSA Spend',

                value:
                    '$'
                    . number_format(
                        $current[
                            'spend'
                        ],
                        2
                    ),

                change:
                    $changes[
                        'spend'
                    ],

                description:
                    'Official LSA account cost'
            ),

            $this->stat(
                label:
                    'Charged Leads',

                value:
                    number_format(
                        $current[
                            'charged_leads'
                        ]
                    ),

                change:
                    $changes[
                        'charged_leads'
                    ],

                description:
                    $current[
                        'total_leads'
                    ]
                    . ' lead records'
            ),

            $this->cplStat(
                $current['cpl'],
                $changes['cpl']
            ),

            $this->stat(
                label:
                    'Phone Calls',

                value:
                    number_format(
                        $current[
                            'phone_calls'
                        ]
                    ),

                change:
                    $changes[
                        'phone_calls'
                    ],

                description:
                    $current[
                        'conversation_calls'
                    ]
                    . ' call conversations'
            ),

            $this->stat(
                label:
                    'Connected Calls',

                value:
                    number_format(
                        $current[
                            'connected_calls'
                        ]
                    ),

                change:
                    $changes[
                        'connected_calls'
                    ],

                description:
                    number_format(
                        $current[
                            'connect_rate'
                        ]
                        * 100,
                        1
                    )
                    . '% connect rate'
            ),

            $this->stat(
                label:
                    'Booked Leads',

                value:
                    number_format(
                        $current[
                            'booked_leads'
                        ]
                    ),

                change:
                    $changes[
                        'booked_leads'
                    ],

                description:
                    number_format(
                        $current[
                            'book_rate'
                        ]
                        * 100,
                        1
                    )
                    . '% of LSA leads'
            ),

            Stat::make(
                'Avg Call Duration',
                $service
                    ->formatDuration(
                        $current[
                            'average_call_duration_ms'
                        ]
                    )
            )
                ->description(
                    $current[
                        'calls_over_30_seconds'
                    ]
                    . ' calls ≥ 30 seconds'
                )
                ->descriptionIcon(
                    Heroicon::OutlinedPhone
                )
                ->color('info'),

            Stat::make(
                'Cost / Connected Call',
                '$'
                . number_format(
                    $current[
                        'cost_per_connected_call'
                    ],
                    2
                )
            )
                ->description(
                    number_format(
                        $current[
                            'connect_rate'
                        ]
                        * 100,
                        1
                    )
                    . '% connected'
                )
                ->descriptionIcon(
                    Heroicon::OutlinedCurrencyDollar
                ),
        ];
    }

    protected function stat(
        string $label,
        string $value,
        float $change,
        string $description
    ): Stat {
        return Stat::make(
            $label,
            $value
        )
            ->description(
                sprintf(
                    '%s · %s%.1f%% vs previous',
                    $description,
                    $change >= 0
                        ? '+'
                        : '',
                    $change
                )
            )
            ->descriptionIcon(
                $change >= 0
                    ? Heroicon::ArrowTrendingUp
                    : Heroicon::ArrowTrendingDown
            )
            ->color(
                $change >= 0
                    ? 'success'
                    : 'danger'
            );
    }

    protected function cplStat(
        float $cpl,
        float $change
    ): Stat {
        /*
         * Lower CPL is good.
         */
        $good =
            $change <= 0;

        return Stat::make(
            'Cost / Charged Lead',
            '$'
            . number_format(
                $cpl,
                2
            )
        )
            ->description(
                sprintf(
                    '%s%.1f%% vs previous',
                    $change >= 0
                        ? '+'
                        : '',
                    $change
                )
            )
            ->descriptionIcon(
                $good
                    ? Heroicon::ArrowTrendingDown
                    : Heroicon::ArrowTrendingUp
            )
            ->color(
                $good
                    ? 'success'
                    : 'danger'
            );
    }

    protected function customerId(
        LsaAnalyticsService $service
    ): ?int {
        return isset(
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
    }
}