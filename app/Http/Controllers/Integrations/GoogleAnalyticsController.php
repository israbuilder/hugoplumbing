<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Integrations\Google\Analytics\AnalyticsAuthService;
use App\Integrations\Google\Analytics\AnalyticsService;
use App\Models\IntegrationAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class GoogleAnalyticsController extends Controller
{
    public function redirect(
        AnalyticsAuthService $auth
    ): RedirectResponse {

        return redirect()->away(
            $auth->getAuthorizationUrl()
        );
    }

    public function callback(
        Request $request,
        AnalyticsAuthService $auth,
        AnalyticsService $analytics
    ): RedirectResponse {

        if (
            $request->has(
                'error'
            )
        ) {

            return redirect()
                ->route(
                    'filament.cms.pages.integrations'
                )
                ->with(
                    'error',
                    'Google Analytics authorization was cancelled.'
                );
        }

        try {

            $auth->validateState(
                $request
                    ->string(
                        'state'
                    )
                    ->toString()
            );

            $code =
                $request
                    ->string(
                        'code'
                    )
                    ->toString();

            if (!$code) {
                throw new RuntimeException(
                    'Google did not return an authorization code.'
                );
            }

            $tokenData =
                $auth->exchangeCode(
                    $code
                );

            $account =
                $auth
                    ->createOrUpdateAccount(
                        $tokenData
                    );

            $properties =
                $analytics
                    ->syncProperties(
                        $account
                    );

            return redirect()
                ->route(
                    'filament.cms.pages.integrations'
                )
                ->with(
                    'success',
                    sprintf(
                        'Google Analytics connected. %d properties found.',
                        $properties
                            ->count()
                    )
                );

        } catch (
            Throwable $e
        ) {

            report($e);

            return redirect()
                ->route(
                    'filament.cms.pages.integrations'
                )
                ->with(
                    'error',
                    'Could not connect Google Analytics: '
                    . $e->getMessage()
                );
        }
    }

    public function disconnect(
        IntegrationAccount $account
    ): RedirectResponse {

        abort_unless(
            $account
                ->integration
                ->provider
                === 'google_analytics',
            404
        );

        if (
            $account->token
        ) {
            $account
                ->token
                ->delete();
        }

        $account->update([
            'status' =>
                'disconnected',
        ]);

        return back()->with(
            'success',
            'Google Analytics disconnected.'
        );
    }
}