<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Integrations\Google\SearchConsole\SearchConsoleAuthService;
use App\Integrations\Google\SearchConsole\SearchConsoleService;
use App\Models\IntegrationAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class GoogleSearchConsoleController extends Controller
{
    public function redirect(
        SearchConsoleAuthService $auth
    ): RedirectResponse {
        return redirect()->away(
            $auth->getAuthorizationUrl()
        );
    }

    public function callback(
        Request $request,
        SearchConsoleAuthService $auth,
        SearchConsoleService $searchConsole
    ): RedirectResponse {
        if ($request->has('error')) {
            return redirect()
                ->route('filament.cms.pages.dashboard')
                ->with(
                    'error',
                    'Google authorization was cancelled: ' .
                    $request->string('error')
                );
        }

        try {

            $auth->validateState(
                $request->string('state')->toString()
            );

            $code =
                $request
                    ->string('code')
                    ->toString();

            if (!$code) {
                throw new \RuntimeException(
                    'Google did not return an authorization code.'
                );
            }

            $tokenData =
                $auth->exchangeCode($code);

            $account =
                $auth->createAccount($tokenData);

            /*
             * Immediately discover properties.
             */
            $sites =
                $searchConsole
                    ->syncSites($account);

            return redirect()
                ->route(
                    'filament.cms.pages.dashboard'
                )
                ->with(
                    'success',
                    sprintf(
                        'Google Search Console connected. %d properties found.',
                        $sites->count()
                    )
                );

        } catch (Throwable $e) {

            report($e);

            return redirect()
                ->route(
                    'filament.cms.pages.dashboard'
                )
                ->with(
                    'error',
                    'Could not connect Search Console: ' .
                    $e->getMessage()
                );
        }
    }

    public function disconnect(
        IntegrationAccount $account
    ): RedirectResponse {
        abort_unless(
            $account->integration->provider
                === 'google_search_console',
            404
        );

        $account->update([
            'status' => 'disconnected',
        ]);

        if ($account->token) {
            $account->token->delete();
        }

        return back()->with(
            'success',
            'Google Search Console disconnected.'
        );
    }
}