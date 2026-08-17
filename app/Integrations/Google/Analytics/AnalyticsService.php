<?php

namespace App\Integrations\Google\Analytics;

use App\Models\AnalyticsProperty;
use App\Models\IntegrationAccount;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function __construct(
        protected AnalyticsAdminClient $admin
    ) {
    }

    public function syncProperties(
        IntegrationAccount $account
    ): Collection {

        $summaries =
            $this->admin
                ->accountSummaries(
                    $account
                );

        $propertyIds = [];

        foreach (
            $summaries
            as $accountSummary
        ) {

            $accountResource =
                $accountSummary[
                    'account'
                ]
                ?? null;

            $accountId =
                $accountResource
                    ? str_replace(
                        'accounts/',
                        '',
                        $accountResource
                    )
                    : null;

            $accountName =
                $accountSummary[
                    'displayName'
                ]
                ?? null;

            foreach (
                $accountSummary[
                    'propertySummaries'
                ]
                ?? []
                as $propertySummary
            ) {

                $propertyResource =
                    $propertySummary[
                        'property'
                    ];

                $propertyId =
                    str_replace(
                        'properties/',
                        '',
                        $propertyResource
                    );

                /*
                 * Fetch complete property metadata:
                 * timezone, currency, etc.
                 */
                $details =
                    $this->admin
                        ->property(
                            $account,
                            $propertyResource
                        );

                $property =
                    AnalyticsProperty::updateOrCreate(
                        [
                            'integration_account_id' =>
                                $account->id,

                            'property_id' =>
                                $propertyId,
                        ],
                        [
                            'property_name' =>
                                $propertyResource,

                            'display_name' =>
                                $details[
                                    'displayName'
                                ]
                                ?? $propertySummary[
                                    'displayName'
                                ]
                                ?? null,

                            'account_id' =>
                                $accountId,

                            'account_name' =>
                                $accountName,

                            'time_zone' =>
                                $details[
                                    'timeZone'
                                ]
                                ?? null,

                            'currency_code' =>
                                $details[
                                    'currencyCode'
                                ]
                                ?? null,

                            'property_type' =>
                                $details[
                                    'propertyType'
                                ]
                                ?? $propertySummary[
                                    'propertyType'
                                ]
                                ?? null,

                            'is_active' =>
                                true,

                            'metadata' => [
                                'summary' =>
                                    $propertySummary,

                                'property' =>
                                    $details,
                            ],
                        ]
                    );

                $propertyIds[] =
                    $property->id;
            }
        }

        if (
            !empty(
                $propertyIds
            )
        ) {

            $account
                ->analyticsProperties()
                ->whereNotIn(
                    'id',
                    $propertyIds
                )
                ->update([
                    'is_active' =>
                        false,
                ]);
        }

        if (
            empty(
                $propertyIds
            )
        ) {
            return collect();
        }

        return AnalyticsProperty::query()
            ->whereIn(
                'id',
                $propertyIds
            )
            ->get();
    }
}