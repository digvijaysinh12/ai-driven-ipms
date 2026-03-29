<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

<<<<<<< HEAD
    // ── Groq AI ────────────────────────────────────────────────────
=======

        /*
    |--------------------------------------------------------------------------
    | Open AI Configuration
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
    ],

<<<<<<< HEAD
    // ── Gemini (optional fallback) ──────────────────────────────────
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

];
=======
];
>>>>>>> 0389c7f0eb061d077a59d46e50c87b9e9e6dab26
