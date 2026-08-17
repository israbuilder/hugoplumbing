<?php

namespace Database\Seeders;

use App\Models\Integration;
use Illuminate\Database\Seeder;

class IntegrationSeeder extends Seeder
{
    public function run(): void
    {
        $integrations = [
            [
                'provider' => 'google_search_console',
                'name' => 'Google Search Console',
                'category' => 'seo',
            ],

            [
                'provider' => 'google_analytics',
                'name' => 'Google Analytics 4',
                'category' => 'analytics',
            ],

            [
                'provider' => 'google_business_profile',
                'name' => 'Google Business Profile',
                'category' => 'local_seo',
            ],

            [
                'provider' => 'google_ads',
                'name' => 'Google Ads',
                'category' => 'paid_search',
            ],

            [
                'provider' => 'google_lsa',
                'name' => 'Google Local Services Ads',
                'category' => 'paid_search',
            ],

            [
                'provider' => 'meta_ads',
                'name' => 'Meta Ads',
                'category' => 'paid_social',
            ],

            [
                'provider' => 'facebook',
                'name' => 'Facebook',
                'category' => 'social',
            ],

            [
                'provider' => 'instagram',
                'name' => 'Instagram',
                'category' => 'social',
            ],

            [
                'provider' => 'youtube',
                'name' => 'YouTube',
                'category' => 'social',
            ],

            [
                'provider' => 'tiktok',
                'name' => 'TikTok',
                'category' => 'social',
            ],
        ];

        foreach ($integrations as $integration) {
            Integration::updateOrCreate(
                [
                    'provider' => $integration['provider'],
                ],
                $integration
            );
        }
    }
}