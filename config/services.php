<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'netlify' => [
    'build_hook_url' => env('NETLIFY_BUILD_HOOK_URL'),
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('GOOGLE_REDIRECT_URI'),
             'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
    ],

    'google_analytics' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_ANALYTICS_REDIRECT_URI'),
    'scope' => ['https://www.googleapis.com/auth/analytics.readonly'],
    ],

    'google_business' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_BUSINESS_PROFILE_REDIRECT_URI'),
        'scope' => ['https://www.googleapis.com/auth/business.manage',],
    ],

    'google_ads' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_ADS_REDIRECT_URI'),
        'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
        'login_customer_id' => env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'),
        'customer_id' => env('GOOGLE_ADS_CUSTOMER_ID'),
        'scope' => ['https://www.googleapis.com/auth/adwords',],
    ],

    'meta' => [
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'redirect' => env('META_REDIRECT_URI'),
        'graph_version' => env('META_GRAPH_VERSION'),
        'scope' => array_filter(array_map('trim',explode(',',
                    env(
                        'META_SCOPES',
                        'ads_read,read_insights,business_management,pages_show_list,pages_read_engagement,instagram_basic,instagram_manage_insights'
                    )))),
    ],
    
    'dataforseo' => [
        'login' => env('DATAFORSEO_LOGIN'),
        'password' => env('DATAFORSEO_PASSWORD'),
        'base_url' => env(
            'DATAFORSEO_BASE_URL',
            'https://api.dataforseo.com'
        ),
    ],

];
    