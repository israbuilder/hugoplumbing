<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Integrations\Google\BusinessProfile\BusinessProfileAuthService;
use App\Integrations\Google\BusinessProfile\BusinessProfileService;
use App\Models\IntegrationAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class GoogleBusinessProfileController extends Controller
{
    public function redirect(
        BusinessProfileAuthService $auth
    ): RedirectResponse {
        return redirect()->away(
            $auth->getAuthorizationUrl()
        );
    }

    public function callback(
        Request $request,
        BusinessProfileAuthService $auth,
        BusinessProfileService $businessProfile
    ): RedirectResponse {
        if ($request->has('error')) {
            return redirect()
                ->route(
                    'filament.cms.pages.integrations'
                )
                ->with(
                    'error',
                    'Google Business Profile authorization cancelled.'
                );
        }

        try {
            $auth->validateState(
                $request
                    ->string('state')
                    ->toString()
            );

            $tokenData =
                $auth->exchangeCode(
                    $request
                        ->string('code')
                        ->toString()
                );

            $account =
                $auth
                    ->createOrUpdateAccount(
                        $tokenData
                    );

            $accounts =
                $businessProfile
                    ->syncAccountsAndLocations(
                        $account
                    );

            return redirect()
                ->route(
                    'filament.cms.pages.integrations'
                )
                ->with(
                    'success',
                    sprintf(
                        'Google Business Profile connected. %d account(s) found.',
                        $accounts->count()
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
                    'Could not connect GBP: '
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
                === 'google_business_profile',
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