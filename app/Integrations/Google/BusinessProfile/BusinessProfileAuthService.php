<?php

namespace App\Integrations\Google\BusinessProfile;

use App\Models\Integration;
use App\Models\IntegrationAccount;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class BusinessProfileAuthService
{
    private const AUTH_URL =
        'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL =
        'https://oauth2.googleapis.com/token';

    public function getAuthorizationUrl(): string
    {
        $config = config(
            'services.google_business'
        );

        $state = Str::random(64);

        session([
            'google_business_profile_oauth_state'
                => $state,
        ]);

        return self::AUTH_URL . '?' . http_build_query([
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
    }

    public function validateState(?string $state): void
    {
        $expected = session()->pull(
            'google_business_profile_oauth_state'
        );

        if (
            !$state ||
            !$expected ||
            !hash_equals(
                $expected,
                $state
            )
        ) {
            throw new RuntimeException(
                'Invalid Business Profile OAuth state.'
            );
        }
    }

    public function exchangeCode(
        string $code
    ): array {
        $config = config(
            'services.google_business'
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
                            $config['client_secret'],

                        'redirect_uri' =>
                            $config['redirect'],

                        'grant_type' =>
                            'authorization_code',
                    ]
                )
                ->throw()
                ->json();

        } catch (RequestException $e) {
            throw new RuntimeException(
                'Business Profile OAuth exchange failed: '
                . $e->response?->body(),
                previous: $e
            );
        }
    }

    public function createOrUpdateAccount(
        array $tokenData
    ): IntegrationAccount {
        return DB::transaction(
            function () use ($tokenData) {

                $integration =
                    Integration::query()
                        ->where(
                            'provider',
                            'google_business_profile'
                        )
                        ->firstOrFail();

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
                                'google_business_profile',

                            'name' =>
                                'Google Business Profile',

                            'status' =>
                                'connected',

                            'connected_at' =>
                                now(),

                            'metadata' => [
                                'provider' => 'google',
                            ],
                        ]);
                } else {
                    $account->update([
                        'status' => 'connected',
                        'connected_at' => now(),
                    ]);
                }

                $existingToken =
                    $account->token;

                $refreshToken =
                    $tokenData['refresh_token']
                    ?? $existingToken?->refresh_token;

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
                                            $tokenData['scope']
                                        )
                                    )
                                    : config(
                                        'services.google_business.scope'
                                    ),

                            'expires_at' =>
                                now()->addSeconds(
                                    (int) (
                                        $tokenData[
                                            'expires_in'
                                        ]
                                        ?? 3600
                                    )
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

    public function getValidAccessToken(
        IntegrationAccount $account
    ): string {
        $token = $account->token;

        if (!$token) {
            throw new RuntimeException(
                'Business Profile OAuth token missing.'
            );
        }

        if (!$token->expiresSoon()) {
            return $token->access_token;
        }

        return $this->refreshAccessToken(
            $account
        );
    }

    public function refreshAccessToken(
        IntegrationAccount $account
    ): string {
        $token = $account->token;

        if (!$token?->refresh_token) {
            throw new RuntimeException(
                'Business Profile refresh token missing.'
            );
        }

        $config = config(
            'services.google_business'
        );

        try {
            $data =
                Http::asForm()
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

        } catch (RequestException $e) {
            $account->update([
                'status' =>
                    'reauthorization_required',
            ]);

            throw new RuntimeException(
                'GBP token refresh failed: '
                . $e->response?->body(),
                previous: $e
            );
        }

        $token->update([
            'access_token' =>
                $data['access_token'],

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

        return $token
            ->fresh()
            ->access_token;
    }
}