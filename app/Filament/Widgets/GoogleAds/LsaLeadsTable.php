<?php

namespace App\Filament\Widgets\GoogleAds;

use App\Services\Analytics\LsaAnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class LsaLeadsTable extends Widget
{
    use InteractsWithPageFilters;

    protected string $view =
        'filament.widgets.google-ads.lsa-leads-table';

    protected int|string|array $columnSpan =
        'full';

    public function getRows(): Collection
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
            return collect();
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

        return $service
            ->recentLeads(
                $customerId,
                $start,
                $end,
                30
            );
    }

    public function statusColor(
        ?string $status
    ): string {
        return match ($status) {

            'BOOKED' =>
                'success',

            'ACTIVE' =>
                'info',

            'NEW' =>
                'warning',

            'DECLINED',
            'CONSUMER_DECLINED' =>
                'danger',

            'EXPIRED',
            'DISABLED',
            'WIPED_OUT' =>
                'gray',

            default =>
                'gray',
        };
    }

    public function typeColor(
        ?string $type
    ): string {
        return match ($type) {

            'PHONE_CALL' =>
                'success',

            'MESSAGE' =>
                'info',

            'BOOKING' =>
                'warning',

            default =>
                'gray',
        };
    }

    public function creditColor(
        ?string $state
    ): string {
        if (!$state) {
            return 'gray';
        }

        if (
            str_contains(
                $state,
                'APPROVED'
            )
            ||
            str_contains(
                $state,
                'CREDITED'
            )
        ) {
            return 'success';
        }

        if (
            str_contains(
                $state,
                'PENDING'
            )
        ) {
            return 'warning';
        }

        if (
            str_contains(
                $state,
                'REJECT'
            )
        ) {
            return 'danger';
        }

        return 'gray';
    }
}