<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Integrations\Meta\MetaApiClient;
use App\Integrations\Meta\MetaAuthService;
use App\Models\Integration;
use App\Models\MetaConnection;
use Illuminate\Http\Request;
use Throwable;

class MetaController extends Controller
{
    public function connect(
        MetaAuthService $auth
    ) {
        return redirect(
            $auth->authorizationUrl()
        );
    }

    public function callback(
        Request $request,
        MetaAuthService $auth,
        MetaApiClient $api
    ) {
        try {

            if ($request->filled('error')) {
                throw new \RuntimeException(
                    $request->string(
                        'error_description'
                    )->toString()
                );
            }

            abort_unless(
                hash_equals(
                    (string) session(
                        'meta_oauth_state'
                    ),
                    (string) $request->state
                ),
                403,
                'Invalid Meta OAuth state.'
            );

            $token = $auth->exchangeCode(
                $request->string('code')->toString()
            );

            $longToken =
                $auth->getLongLivedToken(
                    $token['access_token']
                );

            $accessToken =
                $longToken['access_token'];

            $user = $api->get(
                '/me',
                $accessToken,
                [
                    'fields' => 'id,name',
                ]
            );

            $integration =
                Integration::where(
                    'provider',
                    'facebook'
                )->firstOrFail();

            MetaConnection::updateOrCreate(
                [
                    'integration_id' =>
                        $integration->id,

                    'meta_user_id' =>
                        $user['id'],
                ],
                [
                    'name' =>
                        $user['name'] ?? null,

                    'access_token' =>
                        $accessToken,

                    'token_expires_at' =>
                        isset($longToken['expires_in'])
                            ? now()->addSeconds(
                                $longToken['expires_in']
                            )
                            : null,

                    'scopes' =>
                        config(
                            'services.meta.scope'
                        ),

                    'is_active' => true,

                    'last_error' => null,
                ]
            );

            session()->forget(
                'meta_oauth_state'
            );

            return redirect()
                ->route('filament.cms.pages.integrations')
                ->with(
                    'success',
                    'Meta connected successfully.'
                );

        } catch (Throwable $e) {

            report($e);

            return redirect()
                ->route('filament.cms.pages.integrations')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}