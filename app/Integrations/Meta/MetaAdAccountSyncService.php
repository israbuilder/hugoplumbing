<?php

namespace App\Integrations\Meta;

use App\Models\MetaAdAccount;
use App\Models\MetaConnection;

class MetaAdAccountSyncService
{
    public function __construct(
        protected MetaApiClient $api
    ) {}

    public function sync(
        MetaConnection $connection
    ): void {

        $accounts = $this->api->getAll(
            '/me/adaccounts',
            $connection->access_token,
            [
                'fields' => implode(',', [
                    'id',
                    'account_id',
                    'name',
                    'currency',
                    'timezone_name',
                    'timezone_offset_hours_utc',
                    'account_status',
                    'disable_reason',
                    'balance',
                    'amount_spent',
                ]),
                'limit' => 100,
            ]
        );

        foreach ($accounts as $account) {

            MetaAdAccount::updateOrCreate(
                [
                    'meta_ad_account_id' =>
                        $account['id'],
                ],
                [
                    'meta_connection_id' =>
                        $connection->id,

                    'account_id' =>
                        $account['account_id'] ?? null,

                    'name' =>
                        $account['name'] ?? null,

                    'currency' =>
                        $account['currency'] ?? null,

                    'timezone_name' =>
                        $account['timezone_name'] ?? null,

                    'timezone_offset_hours_utc' =>
                        $account[
                            'timezone_offset_hours_utc'
                        ] ?? null,

                    'account_status' =>
                        $account['account_status'] ?? null,

                    'disable_reason' =>
                        $account['disable_reason'] ?? null,

                    'balance' =>
                        isset($account['balance'])
                            ? $account['balance'] / 100
                            : null,

                    'amount_spent' =>
                        isset($account['amount_spent'])
                            ? $account['amount_spent'] / 100
                            : null,

                    'raw' => $account,
                ]
            );
        }
    }
}