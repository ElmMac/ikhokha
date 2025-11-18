<?php
return [

    /*
    |--------------------------------------------------------------------------
    | iKhokha Base URL
    |--------------------------------------------------------------------------
    | Full URL to iKhokha's API endpoint used for creating paylinks.
    | Example: https://api-sandbox.ikhokha.com/v1/paylinks
    */
    'base_url' => env(key: 'IKHOKHA_API_URL', default: 'https://api.ikhokha.com/public-api/v1/api/payment'),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    */
    'entity_id' => env(key: 'IKHOKHA_APP_ID'),
    'app_secret' => env(key: 'IKHOKHA_APP_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Currency & Mode
    |--------------------------------------------------------------------------
    */
    'currency' => env('IKHOKHA_CURRENCY', 'ZAR'),
    'mode' => env('IKHOKHA_MODE', 'live'), // sandbox|live

    /*
    |--------------------------------------------------------------------------
    | Webhook endpoint (can be overridden via .env)
    |--------------------------------------------------------------------------
    */
    'webhook_endpoint' => env('IKHOKHA_WEBHOOK_ENDPOINT', '/api/ikhokha/webhook'),

    /*
    |--------------------------------------------------------------------------
    | Additional options
    |--------------------------------------------------------------------------
    */
    'timeout' => env('IKHOKHA_TIMEOUT', 10),

    /*|--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------*/
    'urls' => [
        'requester_url' => env(key: 'APP_URL') . '/ikhokha-initiate', // the page that requested payment
        'callback_url' => env(key: 'APP_URL') . env(key: 'IKHOKHA_WEBHOOK_ENDPOINT', default: '/api/ikhokha/webhook'), // webhook
        'success_url' => env(key: 'APP_URL') . '/ikhokha/success',
        'failure_url' => env(key: 'APP_URL') . '/ikhokha/failed',
        'cancel_url' => env(key: 'APP_URL') . '/ikhokha/cancel',

    ],
];
