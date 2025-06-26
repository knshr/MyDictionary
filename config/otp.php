<?php

return [
    // OTP code expiration in minutes
    'expires_in' => env('OTP_EXPIRES_IN', 10),

    // Maximum OTP requests per user per time window
    'max_requests' => env('OTP_MAX_REQUESTS', 5),

    // Time window for max requests (in minutes)
    'request_window' => env('OTP_REQUEST_WINDOW', 30),

    // OTP code length
    'length' => env('OTP_LENGTH', 6),

    // Resend cooldown in seconds
    'resend_cooldown' => env('OTP_RESEND_COOLDOWN', 60),
];
