<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Integrations\Google\Ads\GoogleAdsAuthService;
use App\Integrations\Google\Ads\GoogleAdsService;
use App\Models\IntegrationAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class GoogleAdsController extends Controller
{
    public function redirect(
        GoogleAdsAuthService $auth
    ): RedirectResponse {
        return redirect()->away(
            $auth->getAuthorizationUrl()
        );
    }

    public function callback(
        Request $request,
        GoogleAdsAuthService $auth,
        GoogleAdsService $ads
    ): RedirectResponse {
        if ($request->has('error')) {

            return redirect()
                ->route(
                    'filament.cms.pages.integrations'
                )
                ->with(
                    'error',
                    'Google Ads authorization cancelled.'
                );
        }

        try {
            $auth->validateState(
                $request
                    ->string('state')
                    ->toString()
            );

            $code =
                $request
                    ->string('code')
                    ->toString();

            if (!$code) {
                throw new \RuntimeException(
                    'Authorization code missing.'
                );
            }

            $tokens =
                $auth->exchangeCode(
                    $code
                );

            $account =
                $auth->createOrUpdateAccount(
                    $tokens
                );

            $customers =
                $ads->syncCustomers(
                    $account
                );

            return redirect()
                ->route(
                    'filament.cms.pages.integrations'
                )
                ->with(
                    'success',
                    sprintf(
                        'Google Ads connected. %d account(s) found.',
                        $customers->count()
                    )
                );

        } catch (Throwable $e) {

            report($e);

            return redirect()
                ->route(
                    'filament.cms.pages.integrations'
                )
                ->with(
                    'error',
                    'Google Ads connection failed: '
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
                === 'google_ads',
            404
        );

        $account->token?->delete();

        $account->update([
            'status' =>
                'disconnected',
        ]);

        return back();
    }
}