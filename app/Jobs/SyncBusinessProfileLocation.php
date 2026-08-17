<?php

namespace App\Jobs;

use App\Integrations\Google\BusinessProfile\BusinessProfileSyncService;
use App\Models\BusinessProfileLocation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncBusinessProfileLocation implements ShouldQueue
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
        public int $locationId,
        public ?string $from = null,
        public ?string $to = null,
    ) {
    }

    public function handle(
        BusinessProfileSyncService $sync
    ): void {
        $location =
            BusinessProfileLocation::query()
                ->with([
                    'businessProfileAccount.integrationAccount.token',
                ])
                ->findOrFail(
                    $this->locationId
                );

        if (!$location->is_active) {
            return;
        }

        $from =
            $this->from
            ?? now()
                ->subDays(30)
                ->startOfMonth()
                ->toDateString();

        $to =
            $this->to
            ?? now()
                ->subMonth()
                ->endOfMonth()
                ->toDateString();

        $sync->sync(
            $location,
            $from,
            $to
        );
    }
}