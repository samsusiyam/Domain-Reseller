<?php

return [
    'default' => env('DEFAULT_CURRENCY', 'USD'),
    'primary' => 'USD',
    'secondary' => 'BDT',

    'supported' => [
        'USD' => [
            'name' => 'US Dollar',
            'symbol' => '$',
            'format' => '${amount}',
            'precision' => 2,
        ],
        'BDT' => [
            'name' => 'Bangladeshi Taka',
            'symbol' => '৳',
            'format' => '৳{amount}',
            'precision' => 2,
        ],
    ],

    'exchange_provider' => env('EXCHANGE_PROVIDER', 'openexchangerates'),
    'api_key' => env('CURRENCY_EXCHANGE_API_KEY'),
    'fallback_usd_to_bdt' => (float) env('USD_TO_BDT_RATE', 120.00),
    'margin_percentage' => (float) env('CURRENCY_MARGIN_PERCENT', 1.5),
];
