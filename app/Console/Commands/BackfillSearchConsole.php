<?php

namespace App\Console\Commands;

use App\Integrations\Google\SearchConsole\SearchConsoleBackfillService;
use App\Models\SearchConsoleSite;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class BackfillSearchConsole extends Command
{
    protected $signature = '
        search-console:backfill
        {--site= : Search Console site ID}
        {--months=16 : Number of historical months}
        {--from= : Explicit start date YYYY-MM-DD}
        {--to= : Explicit end date YYYY-MM-DD}
        {--all : Backfill all active properties}
    ';

    protected $description =
        'Queue historical Google Search Console data';

    public function handle(
        SearchConsoleBackfillService $service
    ): int {
        $sites =
            $this->resolveSites();

        if ($sites->isEmpty()) {
            $this->error(
                'No Search Console sites found.'
            );

            return self::FAILURE;
        }

        [
            $from,
            $to
        ] = $this->resolveDates();

        $this->newLine();

        $this->info(
            'Google Search Console Backfill'
        );

        $this->line(
            "From: {$from->toDateString()}"
        );

        $this->line(
            "To:   {$to->toDateString()}"
        );

        $this->line(
            "Sites: {$sites->count()}"
        );

        $this->newLine();

        foreach ($sites as $site) {

            try {

                $this->line(
                    "Creating backfill for {$site->site_url}..."
                );

                $backfill =
                    $service->create(
                        $site,
                        $from,
                        $to
                    );

                $this->info(
                    sprintf(
                        'Backfill #%d created: %d queue jobs.',
                        $backfill->id,
                        $backfill->total_chunks
                    )
                );

            } catch (Throwable $e) {

                report($e);

                $this->error(
                    sprintf(
                        '%s: %s',
                        $site->site_url,
                        $e->getMessage()
                    )
                );
            }
        }

        $this->newLine();

        $this->info(
            'Backfill jobs queued.'
        );

        $this->comment(
            'Run php artisan queue:work to process them.'
        );

        return self::SUCCESS;
    }

    protected function resolveSites()
    {
        $query =
            SearchConsoleSite::query()
                ->where(
                    'is_active',
                    true
                );

        if ($this->option('site')) {

            return $query
                ->where(
                    'id',
                    $this->option('site')
                )
                ->get();
        }

        if ($this->option('all')) {
            return $query->get();
        }

        /*
         * Default:
         * primary property if available,
         * otherwise first active property.
         */
        $site =
            $query
                ->orderByDesc(
                    'is_primary'
                )
                ->first();

        return $site
            ? collect([$site])
            : collect();
    }

    protected function resolveDates(): array
    {
        $to =
            $this->option('to')
                ? Carbon::parse(
                    $this->option('to')
                )
                : now()->subDays(2);

        if ($this->option('from')) {

            $from =
                Carbon::parse(
                    $this->option('from')
                );

        } else {

            $months =
                max(
                    1,
                    (int)
                    $this->option('months')
                );

            $from =
                $to
                    ->copy()
                    ->subMonths(
                        $months
                    )
                    ->addDay();
        }

        return [
            $from->startOfDay(),
            $to->startOfDay(),
        ];
    }
}