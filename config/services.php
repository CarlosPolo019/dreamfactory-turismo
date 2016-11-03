<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun'  => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_KEY'),
    ],
    'mandrill' => [
        'secret' => env('MANDRILL_SECRET'),
    ],
    'ses'      => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION'),
    ],
    'stripe'   => [
        'model'  => 'User',
        'secret' => '',
    ],
    'firebase' => [
	'api_key' => env('FIREBASE_API_KEY','AIzaSyC5PJb9pDa05jY3WpiVmZx5txTcHVYtg0w'),
	'auth_domain' => env('FIREBASE_AUTH_DOMAIN','airlinku-1e710.firebaseapp.com'),
	'database_url' => env('FIREBASE_DATABASE_URL','https://airlinku-1e710.firebaseio.com'),
	'storage_bucket' => env('FIREBASE_STORAGE_BUCKET','airlinku-1e710.appspot.com'),
	'secret' => env('FIREBASE_SECRET','sJSALxYV0YIriVZmG40RufEAq1PCW7ugj8MyARxM')
    ],
];
