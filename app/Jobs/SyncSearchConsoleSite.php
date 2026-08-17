<?php

namespace App\Jobs;

use App\Integrations\Google\SearchConsole\SearchConsoleSyncService;
use App\Models\SearchConsoleSite;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncSearchConsoleSite implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public int $siteId,
        public ?string $from = null,
        public ?string $to = null,
    ) {
    }

    public function handle(
        SearchConsoleSyncService $sync
    ): void {
        $site =
            SearchConsoleSite::query()
                ->with([
                    'account.token',
                ])
                ->findOrFail($this->siteId);

        if (!$site->is_active) {
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
                ->subDays(1)
                ->toDateString();

        $sync->sync(
            $site,
            $from,
            $to
        );
    }
}