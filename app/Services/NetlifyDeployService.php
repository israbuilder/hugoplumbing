<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NetlifyDeployService
{
    public function trigger(): Response
    {
        $url = config('services.netlify.build_hook_url');

        if (blank($url)) {
            throw new RuntimeException(
                'The Netlify build hook URL is not configured.'
            );
        }

        return Http::timeout(15)
            ->retry(2, 500)
            ->post($url)
            ->throw();
    }
}