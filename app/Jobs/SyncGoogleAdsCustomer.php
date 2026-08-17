<?php

namespace App\Jobs;

use App\Integrations\Google\Ads\GoogleAdsSyncService;
use App\Models\GoogleAdsCustomer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncGoogleAdsCustomer implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public int $timeout = 1200;

    public array $backoff = [
        30,
        120,
        300,
        600,
    ];

    public function __construct(
        public int $customerId,
        public ?string $from = null,
        public ?string $to = null,
    ) {
    }

    public function handle(
        GoogleAdsSyncService $sync
    ): void {
        $customer =
            GoogleAdsCustomer::query()
                ->with([
                    'integrationAccount.token',
                    'campaigns',
                ])
                ->findOrFail(
                    $this->customerId
                );

        if (!$customer->is_active) {
            return;
        }

        $sync->sync(
            $customer,

            $this->from
                ?? now()
                    ->subDays(7)
                    ->toDateString(),

            $this->to
                ?? now()
                    ->toDateString()
        );
    }
}