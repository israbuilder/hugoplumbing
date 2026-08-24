<?php

namespace App\Integrations\Google\Ads;

use App\Models\GoogleAdsCampaign;
use App\Models\GoogleAdsCustomer;
use App\Models\GoogleAdsLsaLead;
use App\Models\SyncRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class GoogleAdsSyncService
{
    public function __construct(
        protected GoogleAdsClient $client,
        protected LocalServicesReportingClient $lsaReports
    ) {
    }

    public function sync(
        GoogleAdsCustomer $customer,
        Carbon|string $from,
        Carbon|string $to
    ): SyncRun {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        $account =
            $customer->integrationAccount;

        $run = SyncRun::create([
            'integration_account_id' =>
                $account->id,

            'type' => 'google_ads',

            'status' =>
                SyncRun::STATUS_PENDING,

            'date_from' => $from,

            'date_to' => $to,
        ]);

        $run->markRunning();

        try {
            $count = 0;

            $count +=
                $this->syncCampaigns(
                    $customer
                );

            $count +=
                $this->syncCampaignMetrics(
                    $customer,
                    $from,
                    $to
                );

            $count +=
                $this->syncLsaLeads(
                    $customer,
                    $from,
                    $to
                );

            $count +=
                $this->syncLsaAccountMetrics(
                    $customer,
                    $from,
                    $to
                );

            $customer->update([
                'last_synced_at' => now(),
            ]);

            $account->update([
                'last_synced_at' => now(),
            ]);

            $run->update([
                'status' =>
                    SyncRun::STATUS_SUCCESS,

                'rows_processed' => $count,

                'finished_at' => now(),
            ]);

        } catch (Throwable $e) {

            $run->markFailed($e);

            throw $e;
        }

        return $run->fresh();
    }

    public function syncCampaigns(
        GoogleAdsCustomer $customer
    ): int {
        $rows = $this->client->searchStream(
            $customer->integrationAccount,
            $customer->customer_id,
            <<<'GAQL'
SELECT
    campaign.resource_name,
    campaign.id,
    campaign.name,
    campaign.status,
    campaign.advertising_channel_type,
    campaign.bidding_strategy_type,
    campaign_budget.resource_name,
    campaign_budget.id,
    campaign_budget.amount_micros,
    campaign_budget.period
FROM campaign
WHERE campaign.status != 'REMOVED'
GAQL
        );

        $seen = [];

        foreach ($rows as $row) {

            $campaign =
                $row['campaign']
                ?? [];

            $budget =
                $row['campaignBudget']
                ?? [];

            if (
                empty(
                    $campaign['id']
                )
            ) {
                continue;
            }

            $id =
                (string)
                $campaign['id'];

            $seen[] = $id;

            GoogleAdsCampaign::updateOrCreate(
                [
                    'google_ads_customer_id' =>
                        $customer->id,

                    'campaign_id' => $id,
                ],
                [
                    'resource_name' =>
                        $campaign[
                            'resourceName'
                        ],

                    'name' =>
                        $campaign['name']
                        ?? null,

                    'status' =>
                        $campaign['status']
                        ?? null,

                    'advertising_channel_type' =>
                        $campaign[
                            'advertisingChannelType'
                        ]
                        ?? null,

                    'bidding_strategy_type' =>
                        $campaign[
                            'biddingStrategyType'
                        ]
                        ?? null,

                    'budget_resource_name' =>
                        $budget[
                            'resourceName'
                        ]
                        ?? null,

                    'budget_id' =>
                        isset(
                            $budget['id']
                        )
                            ? (string)
                                $budget['id']
                            : null,

                    'budget_amount_micros' =>
                        isset(
                            $budget[
                                'amountMicros'
                            ]
                        )
                            ? (int)
                                $budget[
                                    'amountMicros'
                                ]
                            : null,

                    'budget_period' =>
                        $budget['period']
                        ?? null,

                    'is_local_services' =>
                        (
                            $campaign[
                                'advertisingChannelType'
                            ]
                            ?? null
                        ) === 'LOCAL_SERVICES',

                    'is_active' =>
                        true,

                    'metadata' =>
                        $row,

                    'last_synced_at' =>
                        now(),
                ]
            );
        }

        if ($seen) {
            $customer
                ->campaigns()
                ->whereNotIn(
                    'campaign_id',
                    $seen
                )
                ->update([
                    'is_active' => false,
                ]);
        }

        return count($seen);
    }

    protected function syncCampaignMetrics(
        GoogleAdsCustomer $customer,
        Carbon $from,
        Carbon $to
    ): int {
        $query = sprintf(
            <<<'GAQL'
SELECT
    segments.date,
    campaign.id,
    metrics.impressions,
    metrics.clicks,
    metrics.cost_micros,
    metrics.conversions,
    metrics.all_conversions,
    metrics.conversions_value
FROM campaign
WHERE
    campaign.status != 'REMOVED'
    AND segments.date BETWEEN '%s' AND '%s'
GAQL,
            $from->toDateString(),
            $to->toDateString()
        );

        $rows = $this->client->searchStream(
            $customer->integrationAccount,
            $customer->customer_id,
            $query
        );

        $records = [];

        foreach ($rows as $row) {

            $remoteCampaign =
                $row['campaign']
                ?? [];

            $campaign =
                GoogleAdsCampaign::query()
                    ->where(
                        'google_ads_customer_id',
                        $customer->id
                    )
                    ->where(
                        'campaign_id',
                        (string) (
                            $remoteCampaign[
                                'id'
                            ]
                            ?? ''
                        )
                    )
                    ->first();

            if (!$campaign) {
                continue;
            }

            $metrics =
                $row['metrics']
                ?? [];

            $date =
                $row['segments']['date']
                ?? null;

            if (!$date) {
                continue;
            }

            $records[] = [
                'google_ads_campaign_id' =>
                    $campaign->id,

                'date' => $date,

                'impressions' =>
                    (int) (
                        $metrics[
                            'impressions'
                        ]
                        ?? 0
                    ),

                'clicks' =>
                    (int) (
                        $metrics[
                            'clicks'
                        ]
                        ?? 0
                    ),

                'cost_micros' =>
                    (int) (
                        $metrics[
                            'costMicros'
                        ]
                        ?? 0
                    ),

                'conversions' =>
                    (float) (
                        $metrics[
                            'conversions'
                        ]
                        ?? 0
                    ),

                'all_conversions' =>
                    (float) (
                        $metrics[
                            'allConversions'
                        ]
                        ?? 0
                    ),

                'conversion_value' =>
                    (float) (
                        $metrics[
                            'conversionsValue'
                        ]
                        ?? 0
                    ),

                'synced_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table(
            'google_ads_campaign_daily_metrics'
        )->upsert(
            $records,
            [
                'google_ads_campaign_id',
                'date',
            ],
            [
                'impressions',
                'clicks',
                'cost_micros',
                'conversions',
                'all_conversions',
                'conversion_value',
                'synced_at',
                'updated_at',
            ]
        );

        return count($records);
    }

    protected function syncLsaLeads(
        GoogleAdsCustomer $customer,
        Carbon $from,
        Carbon $to
    ): int {
        if (
            !$customer
                ->campaigns()
                ->where(
                    'is_local_services',
                    true
                )
                ->exists()
        ) {
            return 0;
        }

        $query = sprintf(
            <<<'GAQL'
SELECT
    local_services_lead.resource_name,
    local_services_lead.id,
    local_services_lead.lead_type,
    local_services_lead.category_id,
    local_services_lead.service_id,
    local_services_lead.contact_details,
    local_services_lead.lead_status,
    local_services_lead.creation_date_time,
    local_services_lead.locale,
    local_services_lead.note.description,
    local_services_lead.note.edit_date_time,
    local_services_lead.lead_charged,
    local_services_lead.credit_details.credit_state,
    local_services_lead.credit_details.credit_state_last_update_date_time,
    local_services_lead.lead_feedback_submitted
FROM local_services_lead
WHERE
    local_services_lead.creation_date_time >= '%s 00:00:00'
    AND local_services_lead.creation_date_time <= '%s 23:59:59'
GAQL,
            $from->toDateString(),
            $to->toDateString()
        );

        $rows = $this->client->searchStream(
            $customer->integrationAccount,
            $customer->customer_id,
            $query
        );

        $count = 0;

        foreach ($rows as $row) {

            $remote =
                $row[
                    'localServicesLead'
                ]
                ?? [];

            $resource =
                $remote[
                    'resourceName'
                ]
                ?? null;

            if (!$resource) {
                continue;
            }

            $leadId =
                isset($remote['id'])
                    ? (string) $remote['id']
                    : basename($resource);

            $contact =
                $remote[
                    'contactDetails'
                ]
                ?? [];

            $credit =
                $remote[
                    'creditDetails'
                ]
                ?? [];

            $note =
                $remote['note']
                ?? [];

            $lead =
                GoogleAdsLsaLead::updateOrCreate(
                    [
                        'google_ads_customer_id' =>
                            $customer->id,

                        'lead_id' =>
                            $leadId,
                    ],
                    [
                        'resource_name' =>
                            $resource,

                        'lead_type' =>
                            $remote[
                                'leadType'
                            ]
                            ?? null,

                        'lead_status' =>
                            $remote[
                                'leadStatus'
                            ]
                            ?? null,

                        'category_id' =>
                            $remote[
                                'categoryId'
                            ]
                            ?? null,

                        'service_id' =>
                            $remote[
                                'serviceId'
                            ]
                            ?? null,

                        'locale' =>
                            $remote['locale']
                            ?? null,

                        'contact_phone' =>
                            $contact[
                                'phoneNumber'
                            ]
                            ?? null,

                        'consumer_name' =>
                            $contact[
                                'consumerName'
                            ]
                            ?? null,

                        'phone_extension' =>
                            $contact[
                                'phoneNumberExtension'
                            ]
                            ?? null,

                        'lead_charged' =>
                            (bool) (
                                $remote[
                                    'leadCharged'
                                ]
                                ?? false
                            ),

                        'credit_state' =>
                            $credit[
                                'creditState'
                            ]
                            ?? null,

                        'credit_updated_at' =>
                            $credit[
                                'creditStateLastUpdateDateTime'
                            ]
                            ?? null,

                        'feedback_submitted' =>
                            (bool) (
                                $remote[
                                    'leadFeedbackSubmitted'
                                ]
                                ?? false
                            ),

                        'note' =>
                            $note[
                                'description'
                            ]
                            ?? null,

                        'note_updated_at' =>
                            $note[
                                'editDateTime'
                            ]
                            ?? null,

                        'lead_created_at' =>
                            $remote[
                                'creationDateTime'
                            ]
                            ?? null,

                        'last_google_update_at' =>
                            now(),

                        'metadata' =>
                            $remote,
                    ]
                );

            $this->syncLeadConversations(
                $customer,
                $lead
            );

            $count++;
        }

        return $count;
    }

    protected function syncLeadConversations(
        GoogleAdsCustomer $customer,
        GoogleAdsLsaLead $lead
    ): int {
        $query = sprintf(
            <<<'GAQL'
SELECT
    local_services_lead_conversation.id,
    local_services_lead_conversation.resource_name,
    local_services_lead_conversation.conversation_channel,
    local_services_lead_conversation.participant_type,
    local_services_lead_conversation.lead,
    local_services_lead_conversation.event_date_time,
    local_services_lead_conversation.phone_call_details.call_duration_millis,
    local_services_lead_conversation.phone_call_details.call_recording_url,
    local_services_lead_conversation.message_details.text,
    local_services_lead_conversation.message_details.attachment_urls
FROM local_services_lead_conversation
WHERE local_services_lead.id = %s
GAQL,
            $lead->lead_id
        );

        $rows = $this->client->searchStream(
            $customer->integrationAccount,
            $customer->customer_id,
            $query
        );

        foreach ($rows as $row) {

            $remote =
                $row[
                    'localServicesLeadConversation'
                ]
                ?? [];

            if (
                empty(
                    $remote['id']
                )
            ) {
                continue;
            }

            $phone =
                $remote[
                    'phoneCallDetails'
                ]
                ?? [];

            $message =
                $remote[
                    'messageDetails'
                ]
                ?? [];

            $lead
                ->conversations()
                ->updateOrCreate(
                    [
                        'conversation_id' =>
                            (string)
                            $remote['id'],
                    ],
                    [
                        'resource_name' =>
                            $remote[
                                'resourceName'
                            ]
                            ?? '',

                        'channel' =>
                            $remote[
                                'conversationChannel'
                            ]
                            ?? null,

                        'participant_type' =>
                            $remote[
                                'participantType'
                            ]
                            ?? null,

                        'call_duration_millis' =>
                            isset(
                                $phone[
                                    'callDurationMillis'
                                ]
                            )
                                ? (int)
                                    $phone[
                                        'callDurationMillis'
                                    ]
                                : null,

                        'call_recording_url' =>
                            $phone[
                                'callRecordingUrl'
                            ]
                            ?? null,

                        'message_text' =>
                            $message['text']
                            ?? null,

                        'attachment_urls' =>
                            $message[
                                'attachmentUrls'
                            ]
                            ?? [],

                        'event_at' =>
                            $remote[
                                'eventDateTime'
                            ]
                            ?? null,

                        'metadata' =>
                            $remote,
                    ]
                );
        }

        return count($rows);
    }

    protected function syncLsaAccountMetrics(
        GoogleAdsCustomer $customer,
        Carbon $from,
        Carbon $to
    ): int {
        /*
         * AccountReports need an MCC.
         */
        if (
            !config(
                'services.google_ads.login_customer_id'
            )
        ) {
            return 0;
        }

        $account =
            $customer
                ->integrationAccount;

        $count = 0;

        $cursor =
            $from->copy()
                ->startOfDay();

        while (
            $cursor->lte($to)
        ) {
            $day =
                $cursor->copy();

            $report =
                $this->lsaReports
                    ->accountReport(
                        $account,
                        $customer
                            ->customer_id,
                        $day,
                        $day
                    );

            if ($report) {

                DB::table(
                    'google_ads_lsa_daily_metrics'
                )->upsert(
                    [
                        [
                            'google_ads_customer_id' =>
                                $customer->id,

                            'date' =>
                                $day
                                    ->toDateString(),

                            'average_weekly_budget' =>
                                $report[
                                    'averageWeeklyBudget'
                                ]
                                ?? null,

                            'rating' =>
                                $report[
                                    'averageFiveStarRating'
                                ]
                                ?? null,

                            'review_count' =>
                                $report[
                                    'totalReview'
                                ]
                                ?? null,

                            'impressions_last_two_days' =>
                                $report[
                                    'impressionsLastTwoDays'
                                ]
                                ?? null,

                            'phone_lead_responsiveness' =>
                                $report[
                                    'phoneLeadResponsiveness'
                                ]
                                ?? null,

                            'charged_leads' =>
                                $report[
                                    'currentPeriodChargedLeads'
                                ]
                                ?? 0,

                            'total_cost' =>
                                $report[
                                    'currentPeriodTotalCost'
                                ]
                                ?? 0,

                            'currency_code' =>
                                $report[
                                    'currencyCode'
                                ]
                                ?? null,

                            'phone_calls' =>
                                $report[
                                    'currentPeriodPhoneCalls'
                                ]
                                ?? 0,

                            'connected_phone_calls' =>
                                $report[
                                    'currentPeriodConnectedPhoneCalls'
                                ]
                                ?? 0,

                            'synced_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ],
                    [
                        'google_ads_customer_id',
                        'date',
                    ],
                    [
                        'average_weekly_budget',
                        'rating',
                        'review_count',
                        'impressions_last_two_days',
                        'phone_lead_responsiveness',
                        'charged_leads',
                        'total_cost',
                        'currency_code',
                        'phone_calls',
                        'connected_phone_calls',
                        'synced_at',
                        'updated_at',
                    ]
                );

                $count++;
            }

            $cursor->addDay();
        }

        return $count;
    }
}