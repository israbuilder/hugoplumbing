<?php

namespace App\Jobs;

use App\Services\NetlifyDeployService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TriggerNetlifyDeploy implements ShouldQueue
{
    use Queueable;

    public function handle(NetlifyDeployService $netlify): void
    {
        $netlify->trigger();
    }
}