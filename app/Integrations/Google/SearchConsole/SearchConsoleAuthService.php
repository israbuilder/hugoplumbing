<?php

namespace App\Integrations\Google\SearchConsole;

use App\Models\Integration;
use App\Models\IntegrationAccount;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SearchConsoleAuthService
{
    private const AUTH_URL =
        'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL =
        'https://oauth2.googleapis.com/token';

    /**
     * Generate Google OAuth authorization URL.
     */
    public function getAuthorizationUrl(): string
    {
        $state = Str::random(64);

        session([
            'google_search_console_oauth_state' => $state,
        ]);

        $config = config('services.google');

        if (
            empty($config['client_id']) ||
            empty($config['client_secret']) ||
            empty($config['redirect'])
        ) {
            throw new RuntimeException(
                'Google Search Console OAuth credentials are not configured.'
            );
        }

        $scopes = $config['scope'] ?? [
            'https://www.googleapis.com/auth/webmasters.readonly',
        ];

        $query = http_build_query([
            'client_id' => $config['client_id'],

            'redirect_uri' => $config['redirect'],

            'response_type' => 'code',

            'scope' => implode(' ', $scopes),

            /*
             * Required if we want a refresh token.
             */
            'access_type' => 'offline',

            /*
             * Forces Google consent screen.
             *
             * Useful while developing because Google does not
             * necessarily return refresh_token on every authorization.
             */
            'prompt' => 'consent',

            'include_granted_scopes' => 'true',

            'state' => $state,
        ]);

        return self::AUTH_URL . '?' . $query;
    }

    /**
     * Validate OAuth state.
     */
    public function validateState(?string $state): void
    {
        $expected = session()->pull(
            'google_search_console_oauth_state'
        );

        if (
            !$state ||
            !$expected ||
            !hash_equals($expected, $state)
        ) {
            throw new RuntimeException(
                'Invalid Google OAuth state.'
            );
        }
    }

    /**
     * Exchange Google authorization code for OAuth tokens.
     */
    public function exchangeCode(string $code): array
    {
        $config = config('services.google');

        if (
            empty($config['client_id']) ||
            empty($config['client_secret']) ||
            empty($config['redirect'])
        ) {
            throw new RuntimeException(
                'Google Search Console OAuth credentials are not configured.'
            );
        }

        try {
            return Http::asForm()
                ->acceptJson()
                ->post(self::TOKEN_URL, [
                    'code' => $code,

                    'client_id' =>
                        $config['client_id'],

                    'client_secret' =>
                        $config['client_secret'],

                    'redirect_uri' =>
                        $config['redirect'],

                    'grant_type' =>
                        'authorization_code',
                ])
                ->throw()
                ->json();

        } catch (RequestException $e) {
            throw new RuntimeException(
                'Could not exchange Google authorization code: ' .
                $e->response?->body(),
                previous: $e
            );
        }
    }

    /**
     * Save OAuth credentials and create the
     * IntegrationAccount.
     */
    public function createAccount(array $tokenData): IntegrationAccount
    {
        return DB::transaction(function () use ($tokenData) {

            $integration = Integration::query()
                ->where(
                    'provider',
                    'google_search_console'
                )
                ->firstOrFail();

            /*
             * Make sure Google returned an access token.
             */
            if (empty($tokenData['access_token'])) {
                throw new RuntimeException(
                    'Google did not return an access token.'
                );
            }

            /*
             * Search Console OAuth doesn't directly give us
             * a Google account ID here.
             *
             * For now, create a CRM integration connection.
             */
            $account = IntegrationAccount::create([
                'integration_id' => $integration->id,

                'name' => 'Google Search Console',

                'status' => 'connected',

                'connected_at' => now(),

                'metadata' => [
                    'provider' => 'google',
                ],
            ]);

            $expiresIn = (int) (
                $tokenData['expires_in'] ?? 3600
            );

            /*
             * Google may or may not include "scope"
             * in the token response.
             */
            $scopes = isset($tokenData['scope'])
                ? preg_split(
                    '/\s+/',
                    trim($tokenData['scope'])
                )
                : config(
                    'services.google.scope',
                    [
                        'https://www.googleapis.com/auth/webmasters.readonly',
                    ]
                );

            $account->token()->create([
                'access_token' =>
                    $tokenData['access_token'],

                /*
                 * refresh_token isn't guaranteed to be present.
                 */
                'refresh_token' =>
                    $tokenData['refresh_token'] ?? null,

                'token_type' =>
                    $tokenData['token_type'] ?? 'Bearer',

                'scopes' => $scopes,

                'expires_at' =>
                    now()->addSeconds($expiresIn),

                'refreshed_at' => now(),
            ]);

            return $account;
        });
    }

    /**
     * Refresh expired access token.
     */
    public function refreshAccessToken(
        IntegrationAccount $account
    ): string {
        $token = $account->token;

        if (!$token) {
            throw new RuntimeException(
                'The integration account does not have an OAuth token.'
            );
        }

        if (!$token->refresh_token) {
            throw new RuntimeException(
                'Google did not return a refresh token. Reconnect Search Console.'
            );
        }

        $config = config('services.google');

        if (
            empty($config['client_id']) ||
            empty($config['client_secret'])
        ) {
            throw new RuntimeException(
                'Google Search Console OAuth credentials are not configured.'
            );
        }

        try {
            $data = Http::asForm()
                ->acceptJson()
                ->post(self::TOKEN_URL, [
                    'client_id' =>
                        $config['client_id'],

                    'client_secret' =>
                        $config['client_secret'],

                    'refresh_token' =>
                        $token->refresh_token,

                    'grant_type' =>
                        'refresh_token',
                ])
                ->throw()
                ->json();

        } catch (RequestException $e) {

            $account->update([
                'status' => 'reauthorization_required',
            ]);

            throw new RuntimeException(
                'Google OAuth token refresh failed: ' .
                $e->response?->body(),
                previous: $e
            );
        }

        if (empty($data['access_token'])) {
            throw new RuntimeException(
                'Google did not return a new access token.'
            );
        }

        $token->update([
            'access_token' =>
                $data['access_token'],

            /*
             * Normally Google does not send a new
             * refresh token when refreshing.
             */
            'refresh_token' =>
                $data['refresh_token']
                ?? $token->refresh_token,

            'token_type' =>
                $data['token_type']
                ?? $token->token_type
                ?? 'Bearer',

            'expires_at' =>
                now()->addSeconds(
                    (int) ($data['expires_in'] ?? 3600)
                ),

            'refreshed_at' =>
                now(),
        ]);

        $account->update([
            'status' => 'connected',
        ]);

        return $token->fresh()->access_token;
    }

    /**
     * Return a valid access token.
     *
     * Refresh automatically if needed.
     */
    public function getValidAccessToken(
        IntegrationAccount $account
    ): string {
        $token = $account->token;

        if (!$token) {
            throw new RuntimeException(
                'OAuth token not found.'
            );
        }

        if ($token->expiresSoon()) {
            return $this->refreshAccessToken(
                $account
            );
        }

        return $token->access_token;
    }
}