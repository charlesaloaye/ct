<?php

return [
    // The owner account created by the seeder. Never hardcode the password.
    'admin' => [
        'name' => env('ADMIN_NAME', 'Charles Aloaye'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    // Where contact-form enquiries are emailed. Defaults to the admin email.
    'contact_recipient' => env('CONTACT_RECIPIENT', env('ADMIN_EMAIL')),

    // Rate limits for public endpoints (per IP).
    'limits' => [
        'contact_per_hour' => (int) env('CONTACT_RATE_PER_HOUR', 5),
        'subscribe_per_hour' => (int) env('SUBSCRIBE_RATE_PER_HOUR', 5),
        'events_per_minute' => (int) env('EVENTS_RATE_PER_MINUTE', 60),
        'login_per_minute' => 5,
    ],
];
