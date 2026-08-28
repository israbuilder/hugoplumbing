<?php

namespace App\Jobs\LocalRank;

use App\Models\LocalRankScan;
use App\Services\LocalRank\Providers\DataForSeoMapsClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SubmitLocalRankScan implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(
        public int $scanId
    ) {
    }

    public function handle(
        DataForSeoMapsClient $client
    ): void {
        $scan = LocalRankScan::with([
            'location',
            'keyword',
            'points',
        ])->findOrFail($this->scanId);

        $scan->update([
            'status' => 'submitting',
        ]);

        $points = $scan->points()
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();

        /*
         * DataForSEO supports multiple tasks in a request.
         * We chunk so we never send a huge request.
         */
        foreach ($points->chunk(100) as $chunk) {
            $payload = [];

            foreach ($chunk as $point) {
                $payload[] = [
                    'keyword' => $scan->keyword->keyword,

                    'location_coordinate' =>
                        $point->latitude . ',' .
                        $point->longitude . ',' .
                        $scan->zoom . 'z',

                    'language_code' =>
                        config('local-rank.language_code', 'en'),

                    'depth' =>
                        config('local-rank.depth', 100),

                    /*
                     * Important for local intent queries.
                     */
                    'search_places' => false,
                    'search_this_area' => true,

                    /*
                     * Lets us associate DataForSEO task -> DB point.
                     */
                    'tag' => 'local_rank_point_' . $point->id,
                ];
            }

            $response = $client->postTasks($payload);

            foreach (($response['tasks'] ?? []) as $task) {
                $tag = data_get($task, 'data.tag');

                if (!$tag) {
                    continue;
                }

                if (
                    !preg_match(
                        '/local_rank_point_(\d+)/',
                        $tag,
                        $matches
                    )
                ) {
                    continue;
                }

                $pointId = (int) $matches[1];

                $point = $chunk->firstWhere('id', $pointId);

                if (!$point) {
                    continue;
                }

               $taskStatusCode = (int) ($task['status_code'] ?? 0);
$taskStatusMessage = $task['status_message'] ?? 'Unknown DataForSEO error';

if ($taskStatusCode !== 20100) {

    $point->update([
        'status' => 'failed',
        'error_message' =>
            "DataForSEO {$taskStatusCode}: {$taskStatusMessage}",
    ]);

    continue;
}

$taskId = $task['id'] ?? null;

if (!$taskId) {

    $point->update([
        'status' => 'failed',
        'error_message' =>
            'DataForSEO created no task ID.',
    ]);

    continue;
}

                $point->update([
                    'provider_task_id' => $taskId,
                    'status' => 'submitted',
                ]);

                $cost = (float) ($task['cost'] ?? 0);

                if ($cost > 0) {
                    $scan->increment(
                        'provider_cost',
                        $cost
                    );
                }

                FetchLocalRankPointResult::dispatch(
                    $point->id
                )->delay(now()->addSeconds(15));
            }
        }

        $scan->update([
            'status' => 'processing',
        ]);
    }

    public function failed(Throwable $exception): void
    {
        LocalRankScan::whereKey($this->scanId)
            ->update([
                'status' => 'failed',
                'meta' => [
                    'error' => $exception->getMessage(),
                ],
                'completed_at' => now(),
            ]);
    }
}