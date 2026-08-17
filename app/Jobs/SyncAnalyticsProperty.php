<?php

namespace App\Jobs;

use App\Integrations\Google\Analytics\AnalyticsSyncService;
use App\Models\AnalyticsProperty;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncAnalyticsProperty implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public int $timeout = 600;

    public array $backoff = [
        30,
        120,
        300,
        600,
    ];

    public function __construct(
        public int $propertyId,
        public ?string $from = null,
        public ?string $to = null,
    ) {
    }

    public function handle(
        AnalyticsSyncService $sync
    ): void {

        $property =
            AnalyticsProperty::query()
                ->with([
                    'account.token',
                ])
                ->findOrFail(
                    $this->propertyId
                );

        if (
            !$property->is_active
        ) {
            return;
        }

        $from =
            $this->from
            ?? now()
                ->subDays(3)
                ->toDateString();

        $to =
            $this->to
            ?? now()
                ->subDay()
                ->toDateString();

        $sync->sync(
            $property,
            $from,
            $to
        );
    }
}