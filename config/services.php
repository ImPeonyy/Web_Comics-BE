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

    'cloudinary' => [
        'cloud_name' => env('do2rk0jz8'),
        'api_key' => env('581922892394899'),
        'api_secret' => env('YU3EfiK9NKB0nI95v1hSkdh6RbY'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

];
