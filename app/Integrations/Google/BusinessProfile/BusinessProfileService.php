<?php

namespace App\Integrations\Google\BusinessProfile;

use App\Models\BusinessProfileAccount;
use App\Models\BusinessProfileLocation;
use App\Models\IntegrationAccount;
use Illuminate\Support\Collection;

class BusinessProfileService
{
    public function __construct(
        protected BusinessProfileAccountClient $accounts,
        protected BusinessProfileInformationClient $information
    ) {
    }

    public function syncAccountsAndLocations(
        IntegrationAccount $integrationAccount
    ): Collection {
        $remoteAccounts =
            $this->accounts
                ->accounts(
                    $integrationAccount
                );

        $accountIds = [];

        foreach (
            $remoteAccounts
            as $remoteAccount
        ) {
            $resource =
                $remoteAccount['name'];

            $accountId =
                str_replace(
                    'accounts/',
                    '',
                    $resource
                );

            $account =
                BusinessProfileAccount::updateOrCreate(
                    [
                        'integration_account_id' =>
                            $integrationAccount->id,

                        'account_id' =>
                            $accountId,
                    ],
                    [
                        'account_name' =>
                            $resource,

                        'display_name' =>
                            $remoteAccount[
                                'accountName'
                            ]
                            ?? null,

                        'account_type' =>
                            $remoteAccount[
                                'type'
                            ]
                            ?? null,

                        'role' =>
                            $remoteAccount[
                                'role'
                            ]
                            ?? null,

                        'verification_state' =>
                            $remoteAccount[
                                'verificationState'
                            ]
                            ?? null,

                        'is_active' =>
                            true,

                        'metadata' =>
                            $remoteAccount,
                    ]
                );

            $accountIds[] =
                $account->id;

            $this->syncLocations(
                $integrationAccount,
                $account
            );
        }

        if (!empty($accountIds)) {
            $integrationAccount
                ->businessProfileAccounts()
                ->whereNotIn(
                    'id',
                    $accountIds
                )
                ->update([
                    'is_active' => false,
                ]);
        }

        return BusinessProfileAccount::query()
            ->whereIn(
                'id',
                $accountIds
            )
            ->with('locations')
            ->get();
    }

    protected function syncLocations(
        IntegrationAccount $integrationAccount,
        BusinessProfileAccount $account
    ): void {
        $locations =
            $this->information
                ->locations(
                    $integrationAccount,
                    $account->account_name
                );

        $ids = [];

        foreach (
            $locations
            as $remote
        ) {
            $resource =
                $remote['name'];

            $locationId =
                str_replace(
                    'locations/',
                    '',
                    $resource
                );

            $address =
                $remote[
                    'storefrontAddress'
                ]
                ?? [];

            $lines =
                $address[
                    'addressLines'
                ]
                ?? [];

            $latLng =
                $remote[
                    'latlng'
                ]
                ?? [];

            $location =
                BusinessProfileLocation::updateOrCreate(
                    [
                        'business_profile_account_id' =>
                            $account->id,

                        'location_id' =>
                            $locationId,
                    ],
                    [
                        'location_name' =>
                            $resource,

                        'title' =>
                            $remote[
                                'title'
                            ]
                            ?? null,

                        'store_code' =>
                            $remote[
                                'storeCode'
                            ]
                            ?? null,

                        'phone' =>
                            $remote[
                                'phoneNumbers'
                            ][
                                'primaryPhone'
                            ]
                            ?? null,

                        'website_uri' =>
                            $remote[
                                'websiteUri'
                            ]
                            ?? null,

                        'primary_category' =>
                            $remote[
                                'categories'
                            ][
                                'primaryCategory'
                            ][
                                'displayName'
                            ]
                            ?? null,

                        'address_line_1' =>
                            $lines[0]
                            ?? null,

                        'address_line_2' =>
                            $lines[1]
                            ?? null,

                        'city' =>
                            $address[
                                'locality'
                            ]
                            ?? null,

                        'region' =>
                            $address[
                                'administrativeArea'
                            ]
                            ?? null,

                        'postal_code' =>
                            $address[
                                'postalCode'
                            ]
                            ?? null,

                        'country_code' =>
                            $address[
                                'regionCode'
                            ]
                            ?? null,

                        'latitude' =>
                            $latLng[
                                'latitude'
                            ]
                            ?? null,

                        'longitude' =>
                            $latLng[
                                'longitude'
                            ]
                            ?? null,

                        'is_active' =>
                            true,

                        'metadata' =>
                            $remote,
                    ]
                );

            $ids[] =
                $location->id;
        }

        if (!empty($ids)) {
            $account
                ->locations()
                ->whereNotIn(
                    'id',
                    $ids
                )
                ->update([
                    'is_active' =>
                        false,
                ]);
        }
    }
}