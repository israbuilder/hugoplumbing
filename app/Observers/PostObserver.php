<?php

namespace App\Observers;

use App\Jobs\TriggerNetlifyDeploy;
use App\Models\Post;

class PostObserver
{
    public function created(Post $post): void
    {
        if ($post->status === 'published') {
            TriggerNetlifyDeploy::dispatch()->afterCommit();
        }
    }

    public function updated(Post $post): void
    {
        $wasPublished = $post->getOriginal('status') === 'published';
        $isPublished = $post->status === 'published';

        if ($wasPublished || $isPublished) {
            TriggerNetlifyDeploy::dispatch()
              ->delay(now()->addSeconds(20))->afterCommit();
        }
    }

    public function deleted(Post $post): void
    {
        if ($post->getOriginal('status') === 'published') {
            TriggerNetlifyDeploy::dispatch()->afterCommit();
        }
    }
}