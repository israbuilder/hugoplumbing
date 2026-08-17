<?php

namespace App\Integrations\Google\Analytics;

use App\Models\Integration;
use App\Models\IntegrationAccount;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AnalyticsAuthService
{
    private const AUTH_URL =
        'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL =
        'https://oauth2.googleapis.com/token';

    public function getAuthorizationUrl(): string
    {
        $config =
            config(
                'services.google_analytics'
            );

        if (
            empty($config['client_id'])
            || empty($config['client_secret'])
            || empty($config['redirect'])
        ) {
            throw new RuntimeException(
                'Google Analytics OAuth credentials are not configured.'
            );
        }

        $state = Str::random(64);

        session([
            'google_analytics_oauth_state'
                => $state,
        ]);

        $query = http_build_query([
            'client_id' =>
                $config['client_id'],

            'redirect_uri' =>
                $config['redirect'],

            'response_type' =>
                'code',

            'scope' =>
                implode(
                    ' ',
                    $config['scope']
                ),

            'access_type' =>
                'offline',

            'prompt' =>
                'consent',

            'include_granted_scopes' =>
                'true',

            'state' =>
                $state,
        ]);

        return self::AUTH_URL
            . '?'
            . $query;
    }

    public function validateState(
        ?string $state
    ): void {
        $expected =
            session()->pull(
                'google_analytics_oauth_state'
            );

        if (
            !$state
            || !$expected
            || !hash_equals(
                $expected,
                $state
            )
        ) {
            throw new RuntimeException(
                'Invalid Google Analytics OAuth state.'
            );
        }
    }

    public function exchangeCode(
        string $code
    ): array {
        $config =
            config(
                'services.google_analytics'
            );

        try {

            return Http::asForm()
                ->acceptJson()
                ->post(
                    self::TOKEN_URL,
                    [
                        'code' =>
                            $code,

                        'client_id' =>
                            $config['client_id'],

                        'client_secret' =>
                            $config[
                                'client_secret'
                            ],

                        'redirect_uri' =>
                            $config[
                                'redirect'
                            ],

                        'grant_type' =>
                            'authorization_code',
                    ]
                )
                ->throw()
                ->json();

        } catch (
            RequestException $e
        ) {

            throw new RuntimeException(
                'Google Analytics OAuth exchange failed: '
                . $e->response?->body(),
                previous: $e
            );
        }
    }

   public function createOrUpdateAccount(
    array $tokenData
): IntegrationAccount {

    if (
        empty($tokenData['access_token'])
    ) {
        throw new RuntimeException(
            'Google did not return an access token.'
        );
    }

    return DB::transaction(
        function () use ($tokenData) {

            $integration =
                Integration::query()
                    ->where(
                        'provider',
                        'google_analytics'
                    )
                    ->firstOrFail();

            /*
             * For now we maintain one Google Analytics
             * connection per integration.
             */
            $account =
                IntegrationAccount::query()
                    ->where(
                        'integration_id',
                        $integration->id
                    )
                    ->first();

            if (!$account) {

                $account =
                    IntegrationAccount::create([
                        'integration_id' =>
                            $integration->id,

                        'external_account_id' =>
                            'google_analytics',

                        'name' =>
                            'Google Analytics',

                        'email' =>
                            null,

                        'status' =>
                            'connected',

                        'connected_at' =>
                            now(),

                        'metadata' => [
                            'provider' =>
                                'google',
                        ],
                    ]);

            } else {

                $account->update([
                    'status' =>
                        'connected',

                    'connected_at' =>
                        now(),
                ]);
            }

            $existingToken =
                $account->token;

            /*
             * Google does not always return a new
             * refresh token on subsequent authorizations.
             */
            $refreshToken =
                $tokenData['refresh_token']
                ?? $existingToken?->refresh_token;

            $expiresIn =
                (int) (
                    $tokenData['expires_in']
                    ?? 3600
                );

            $account
                ->token()
                ->updateOrCreate(
                    [],
                    [
                        'access_token' =>
                            $tokenData[
                                'access_token'
                            ],

                        'refresh_token' =>
                            $refreshToken,

                        'token_type' =>
                            $tokenData[
                                'token_type'
                            ]
                            ?? 'Bearer',

                        'scopes' =>
                            isset(
                                $tokenData['scope']
                            )
                                ? preg_split(
                                    '/\s+/',
                                    trim(
                                        $tokenData[
                                            'scope'
                                        ]
                                    )
                                )
                                : config(
                                    'services.google_analytics.scope'
                                ),

                        'expires_at' =>
                            now()->addSeconds(
                                $expiresIn
                            ),

                        'refreshed_at' =>
                            now(),
                    ]
                );

            return $account->fresh([
                'token',
            ]);
        }
    );
}

    public function refreshAccessToken(
        IntegrationAccount $account
    ): string {
        $token =
            $account->token;

        if (!$token) {
            throw new RuntimeException(
                'Analytics OAuth token not found.'
            );
        }

        if (!$token->refresh_token) {
            throw new RuntimeException(
                'Analytics refresh token is missing. Reconnect Google Analytics.'
            );
        }

        $config =
            config(
                'services.google_analytics'
            );

        try {

            $data =
                Http::asForm()
                    ->acceptJson()
                    ->post(
                        self::TOKEN_URL,
                        [
                            'client_id' =>
                                $config[
                                    'client_id'
                                ],

                            'client_secret' =>
                                $config[
                                    'client_secret'
                                ],

                            'refresh_token' =>
                                $token
                                    ->refresh_token,

                            'grant_type' =>
                                'refresh_token',
                        ]
                    )
                    ->throw()
                    ->json();

        } catch (
            RequestException $e
        ) {

            $account->update([
                'status' =>
                    'reauthorization_required',
            ]);

            throw new RuntimeException(
                'Analytics token refresh failed: '
                . $e->response?->body(),
                previous: $e
            );
        }

        $token->update([
            'access_token' =>
                $data[
                    'access_token'
                ],

            'refresh_token' =>
                $data[
                    'refresh_token'
                ]
                ?? $token
                    ->refresh_token,

            'token_type' =>
                $data[
                    'token_type'
                ]
                ?? $token
                    ->token_type,

            'expires_at' =>
                now()->addSeconds(
                    (int) (
                        $data[
                            'expires_in'
                        ]
                        ?? 3600
                    )
                ),

            'refreshed_at' =>
                now(),
        ]);

        $account->update([
            'status' =>
                'connected',
        ]);

        return $token
            ->fresh()
            ->access_token;
    }

    public function getValidAccessToken(
        IntegrationAccount $account
    ): string {
        $token =
            $account->token;

        if (!$token) {
            throw new RuntimeException(
                'Analytics OAuth token not found.'
            );
        }

        if (
            $token->expiresSoon()
        ) {
            return $this
                ->refreshAccessToken(
                    $account
                );
        }

        return $token
            ->access_token;
    }
}