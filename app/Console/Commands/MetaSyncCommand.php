<?php

namespace App\Console\Commands;

use App\Integrations\Meta\MetaSyncService;
use App\Models\MetaConnection;
use Illuminate\Console\Command;
use Throwable;

class MetaSyncCommand extends Command
{
    protected $signature =
        'meta:sync
        {--days=30 : Number of insight days to sync}';

    protected $description =
        'Synchronize Meta Ads, Facebook and Instagram data';

    public function handle(
        MetaSyncService $sync
    ): int {

        $connections =
            MetaConnection::query()
                ->where('is_active', true)
                ->get();

        foreach ($connections as $connection) {

            try {

                $this->info(
                    "Syncing Meta connection {$connection->id}"
                );

                $sync->sync(
                    $connection,
                    (int) $this->option('days')
                );

                $this->info(
                    'Meta sync complete.'
                );

            } catch (Throwable $e) {

                $this->error(
                    $e->getMessage()
                );

                report($e);
            }
        }

        return self::SUCCESS;
    }
}