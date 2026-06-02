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

    'stripe' => [
        'key'            => env('STRIPE_KEY', ''),
        'secret'         => env('STRIPE_SECRET', ''),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
    ],

    'jazzcash' => [
        'merchant_id'     => env('JAZZCASH_MERCHANT_ID', ''),
        'password'        => env('JAZZCASH_PASSWORD', ''),
        'integrity_salt'  => env('JAZZCASH_INTEGRITY_SALT', ''),
        'endpoint'        => env('JAZZCASH_ENDPOINT', 'https://sandbox.jazzcash.com.pk/ApplicationAPI/API/2.0/Purchase/DoMWalletTransaction'),
        'env'             => env('JAZZCASH_ENV', 'sandbox'),
    ],

    'easypaisa' => [
        'store_id'   => env('EASYPAISA_STORE_ID', ''),
        'hash_key'   => env('EASYPAISA_HASH_KEY', ''),
        'endpoint'   => env('EASYPAISA_ENDPOINT', 'https://easypaystg.easypaisa.com.pk/easypay/Index.jsf'),
        'env'        => env('EASYPAISA_ENV', 'sandbox'),
    ],

];
