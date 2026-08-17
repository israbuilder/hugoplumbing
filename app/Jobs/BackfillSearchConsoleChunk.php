<?php

namespace App\Jobs;

use App\Integrations\Google\SearchConsole\SearchConsoleSyncService;
use App\Models\SearchConsoleBackfill;
use App\Models\SearchConsoleSite;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

class BackfillSearchConsoleChunk implements ShouldQueue
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
        public int $backfillId,
        public int $siteId,
        public string $from,
        public string $to,
    ) {
    }

    public function handle(
        SearchConsoleSyncService $sync
    ): void {
        $backfill =
            SearchConsoleBackfill::query()
                ->findOrFail(
                    $this->backfillId
                );

        $site =
            SearchConsoleSite::query()
                ->with([
                    'account.token',
                ])
                ->findOrFail(
                    $this->siteId
                );

        if (!$site->is_active) {
            throw new \RuntimeException(
                "Search Console site {$site->id} is inactive."
            );
        }

        /*
         * First job changes the backfill
         * from pending to running.
         */
        if (
            $backfill->status ===
            SearchConsoleBackfill::STATUS_PENDING
        ) {
            $backfill->update([
                'status' =>
                    SearchConsoleBackfill::STATUS_RUNNING,

                'started_at' =>
                    $backfill->started_at ?? now(),
            ]);
        }

        $run = $sync->sync(
            $site,
            $this->from,
            $this->to
        );

        /*
         * Atomic increment because multiple
         * queue workers may finish simultaneously.
         */
        DB::transaction(
            function () use (
                $backfill,
                $run
            ) {
                $locked =
                    SearchConsoleBackfill::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $backfill->id
                        );

                $locked->completed_chunks++;

                $locked->rows_processed +=
                    $run->rows_processed;

                $processed =
                    $locked->completed_chunks
                    + $locked->failed_chunks;

                if (
                    $processed >=
                    $locked->total_chunks
                ) {
                    $locked->status =
                        $locked->failed_chunks > 0
                            ? SearchConsoleBackfill::STATUS_PARTIAL
                            : SearchConsoleBackfill::STATUS_COMPLETED;

                    $locked->finished_at =
                        now();
                }

                $locked->save();
            }
        );
    }

    public function failed(
        ?Throwable $exception
    ): void {
        DB::transaction(
            function () use ($exception) {

                $backfill =
                    SearchConsoleBackfill::query()
                        ->lockForUpdate()
                        ->find(
                            $this->backfillId
                        );

                if (!$backfill) {
                    return;
                }

                $backfill->failed_chunks++;

                $processed =
                    $backfill->completed_chunks
                    + $backfill->failed_chunks;

                $backfill->error_message =
                    $exception?->getMessage();

                if (
                    $processed >=
                    $backfill->total_chunks
                ) {
                    $backfill->status =
                        $backfill->completed_chunks > 0
                            ? SearchConsoleBackfill::STATUS_PARTIAL
                            : SearchConsoleBackfill::STATUS_FAILED;

                    $backfill->finished_at =
                        now();
                }

                $backfill->save();
            }
        );
    }
}