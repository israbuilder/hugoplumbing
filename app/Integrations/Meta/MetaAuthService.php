<?php

namespace App\Integrations\Meta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MetaAuthService
{
    public function authorizationUrl(): string
    {
        $state = Str::random(40);

        session([
            'meta_oauth_state' => $state,
        ]);

        return 'https://www.facebook.com/' .
            config('services.meta.graph_version') .
            '/dialog/oauth?' .
            http_build_query([
                'client_id' => config('services.meta.app_id'),

                'redirect_uri' =>
                    config('services.meta.redirect'),

                'state' => $state,

                'scope' => implode(
                    ',',
                    config('services.meta.scope')
                ),

                'response_type' => 'code',
            ]);
    }

    public function exchangeCode(string $code): array
    {
        $response = Http::get(
            'https://graph.facebook.com/' .
            config('services.meta.graph_version') .
            '/oauth/access_token',
            [
                'client_id' =>
                    config('services.meta.app_id'),

                'client_secret' =>
                    config('services.meta.app_secret'),

                'redirect_uri' =>
                    config('services.meta.redirect'),

                'code' => $code,
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Meta OAuth failed: ' .
                $response->body()
            );
        }

        return $response->json();
    }

    public function getLongLivedToken(
        string $shortToken
    ): array {

        $response = Http::get(
            'https://graph.facebook.com/' .
            config('services.meta.graph_version') .
            '/oauth/access_token',
            [
                'grant_type' =>
                    'fb_exchange_token',

                'client_id' =>
                    config('services.meta.app_id'),

                'client_secret' =>
                    config('services.meta.app_secret'),

                'fb_exchange_token' =>
                    $shortToken,
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Meta long lived token failed: ' .
                $response->body()
            );
        }

        return $response->json();
    }
}