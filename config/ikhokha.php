<?php

return [

    /*
   |--------------------------------------------------------------------------
   | iKhokha API Credentials
   |--------------------------------------------------------------------------
   */
    'base_url' => env('IKHOKHA_BASE_URL', 'https://api.ikhokha.com/public-api/v1/api/payment'),
    'entity_id' => env('IKHOKHA_ENTITY_ID'),
    'mode' => env('IKHOKHA_MODE', 'live'),
    'external_entity_id' => env('IKHOKHA_EXTERNAL_ENTITY_ID'),
    'currency' => 'ZAR',
    'app_secret' => env('IKHOKHA_APP_SECRET', ''),
    'urls' => [
        'requester_url' => env('APP_URL') . '/initiate-payment', // the page that requested payment
        'callback_url' => env('APP_URL') . '/api/ikhokha/callback', // webhook
        'success_url' => env('APP_URL') . '/ikhokha/success',
        'failure_url' => env('APP_URL') . '/ikhokha/failed',
        'cancel_url' => env('APP_URL') . '/ikhokha/cancel',
    ],

    /*
    |--------------------------------------------------------------------------
    | iKhokha Webhook Secret (optional)
    |--------------------------------------------------------------------------
    */
    'webhook_secret' => env('IKHOKHA_WEBHOOK_SECRET', ''),

];
