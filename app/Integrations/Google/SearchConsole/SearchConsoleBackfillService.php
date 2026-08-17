<?php

namespace App\Integrations\Google\SearchConsole;

use App\Jobs\BackfillSearchConsoleChunk;
use App\Models\SearchConsoleBackfill;
use App\Models\SearchConsoleSite;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SearchConsoleBackfillService
{
    public const CHUNK_DAYS = 7;

    public function create(
        SearchConsoleSite $site,
        Carbon|string $from,
        Carbon|string $to,
        bool $dispatch = true
    ): SearchConsoleBackfill {
        $from = Carbon::parse($from)
            ->startOfDay();

        $to = Carbon::parse($to)
            ->startOfDay();

        /*
         * We don't want today's potentially
         * incomplete data in a historical import.
         */
        $maxDate =
            now()
                ->subDays(2)
                ->startOfDay();

        if ($to->greaterThan($maxDate)) {
            $to = $maxDate;
        }

        if ($from->greaterThan($to)) {
            throw new InvalidArgumentException(
                'Backfill start date must be before the end date.'
            );
        }

        $chunks =
            $this->makeChunks(
                $from,
                $to
            );

        $backfill =
            SearchConsoleBackfill::create([
                'search_console_site_id' =>
                    $site->id,

                'date_from' =>
                    $from,

                'date_to' =>
                    $to,

                'status' =>
                    SearchConsoleBackfill::STATUS_PENDING,

                'total_chunks' =>
                    $chunks->count(),

                'completed_chunks' =>
                    0,

                'failed_chunks' =>
                    0,

                'rows_processed' =>
                    0,

                'metadata' => [
                    'chunk_days' =>
                        self::CHUNK_DAYS,

                    'created_by' =>
                        'search_console_backfill',
                ],
            ]);

        if ($dispatch) {
            $this->dispatch(
                $backfill,
                $chunks
            );
        }

        return $backfill;
    }

    public function makeChunks(
        Carbon $from,
        Carbon $to
    ): Collection {
        $chunks = collect();

        $cursor =
            $from->copy();

        while (
            $cursor->lessThanOrEqualTo($to)
        ) {
            $chunkStart =
                $cursor->copy();

            $chunkEnd =
                $cursor
                    ->copy()
                    ->addDays(
                        self::CHUNK_DAYS - 1
                    );

            if (
                $chunkEnd->greaterThan($to)
            ) {
                $chunkEnd =
                    $to->copy();
            }

            $chunks->push([
                'from' =>
                    $chunkStart
                        ->toDateString(),

                'to' =>
                    $chunkEnd
                        ->toDateString(),
            ]);

            $cursor =
                $chunkEnd
                    ->copy()
                    ->addDay();
        }

        return $chunks;
    }

    public function dispatch(
        SearchConsoleBackfill $backfill,
        ?Collection $chunks = null
    ): void {
        $chunks ??=
            $this->makeChunks(
                $backfill
                    ->date_from
                    ->copy(),

                $backfill
                    ->date_to
                    ->copy()
            );

        foreach ($chunks as $chunk) {

            BackfillSearchConsoleChunk::dispatch(
                backfillId:
                    $backfill->id,

                siteId:
                    $backfill
                        ->search_console_site_id,

                from:
                    $chunk['from'],

                to:
                    $chunk['to'],
            );
        }
    }
}