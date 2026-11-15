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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'sibob' => [
        'base_url' => rtrim((string) env('SIBOB_BASE_URL', 'https://sibob.bbpombanjarmasin.com'), '/'),
        'service_email' => env('SIBOB_SERVICE_EMAIL', ''),
        'service_password' => env('SIBOB_SERVICE_PASSWORD', ''),
        'timeout' => (int) env('SIBOB_TIMEOUT', 10),
        'verify_ssl' => (bool) env('SIBOB_VERIFY_SSL', false),
    ],

];
