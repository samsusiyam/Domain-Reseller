<?php

return [
    'tiers' => [
        1 => [
            'name' => 'Tier 1 - Standard',
            'min_deposit' => (float) env('RESELLER_MIN_WALLET_DEPOSIT', 10.00),
            'discount_percentage' => 0.0,
        ],
        2 => [
            'name' => 'Tier 2 - Silver',
            'min_deposit' => 100.00,
            'discount_percentage' => 3.0,
        ],
        3 => [
            'name' => 'Tier 3 - Gold',
            'min_deposit' => 500.00,
            'discount_percentage' => 6.0,
        ],
    ],

    'rate_limits' => [
        'requests_per_minute' => (int) env('RESELLER_API_DEFAULT_RATE_LIMIT', 60),
    ],

    'ip_whitelist_max' => 10,

    'api_token_expiry_days' => null, // null for permanent until manually revoked
];
