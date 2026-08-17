<?php

namespace App\Integrations\Meta;

use App\Models\MetaAdAccount;
use App\Models\MetaAdInsightDaily;
use Carbon\Carbon;

class MetaAdsInsightsSyncService
{
    public function __construct(
        protected MetaApiClient $api
    ) {}

    public function sync(
        MetaAdAccount $account,
        Carbon $from,
        Carbon $to,
        string $level = 'ad'
    ): void {

        $rows = $this->api->getAll(
            '/' .
            $account->meta_ad_account_id .
            '/insights',

            $account->connection->access_token,

            [
                'level' => $level,

                'time_increment' => 1,

                'time_range' => json_encode([
                    'since' =>
                        $from->toDateString(),

                    'until' =>
                        $to->toDateString(),
                ]),

                'fields' => implode(',', [
                    'campaign_id',
                    'adset_id',
                    'ad_id',
                    'date_start',
                    'date_stop',
                    'impressions',
                    'reach',
                    'clicks',
                    'unique_clicks',
                    'inline_link_clicks',
                    'spend',
                    'cpc',
                    'cpm',
                    'ctr',
                    'frequency',
                    'actions',
                    'action_values',
                    'cost_per_action_type',
                    'outbound_clicks',
                    'outbound_clicks_ctr',
                    'quality_ranking',
                    'engagement_rate_ranking',
                    'conversion_rate_ranking',
                ]),

                'limit' => 500,
            ]
        );

        foreach ($rows as $row) {

            MetaAdInsightDaily::updateOrCreate(
                [
                    'meta_ad_account_id' =>
                        $account->id,

                    'level' =>
                        $level,

                    'meta_campaign_id' =>
                        $row[
                            'campaign_id'
                        ] ?? null,

                    'meta_ad_set_id' =>
                        $row[
                            'adset_id'
                        ] ?? null,

                    'meta_ad_id' =>
                        $row[
                            'ad_id'
                        ] ?? null,

                    'date' =>
                        $row['date_start'],
                ],
                [
                    'impressions' =>
                        $row[
                            'impressions'
                        ] ?? 0,

                    'reach' =>
                        $row['reach'] ?? 0,

                    'clicks' =>
                        $row['clicks'] ?? 0,

                    'unique_clicks' =>
                        $row[
                            'unique_clicks'
                        ] ?? 0,

                    'inline_link_clicks' =>
                        $row[
                            'inline_link_clicks'
                        ] ?? 0,

                    'spend' =>
                        $row['spend'] ?? 0,

                    'cpc' =>
                        $row['cpc'] ?? null,

                    'cpm' =>
                        $row['cpm'] ?? null,

                    'ctr' =>
                        $row['ctr'] ?? null,

                    'frequency' =>
                        $row[
                            'frequency'
                        ] ?? null,

                    'actions' =>
                        $row[
                            'actions'
                        ] ?? null,

                    'action_values' =>
                        $row[
                            'action_values'
                        ] ?? null,

                    'cost_per_action_type' =>
                        $row[
                            'cost_per_action_type'
                        ] ?? null,

                    'outbound_clicks' =>
                        $row[
                            'outbound_clicks'
                        ] ?? null,

                    'outbound_clicks_ctr' =>
                        $row[
                            'outbound_clicks_ctr'
                        ] ?? null,

                    'quality_ranking' =>
                        $row[
                            'quality_ranking'
                        ] ?? null,

                    'engagement_rate_ranking' =>
                        $row[
                            'engagement_rate_ranking'
                        ] ?? null,

                    'conversion_rate_ranking' =>
                        $row[
                            'conversion_rate_ranking'
                        ] ?? null,

                    'raw' => $row,
                ]
            );
        }
    }
}