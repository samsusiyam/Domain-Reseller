<?php

return [
    'default' => env('DEFAULT_PAYMENT_GATEWAY', 'bkash'),

    'gateways' => [
        'manual' => [
            'name' => 'Manual Payment',
            'enabled' => env('GATEWAY_MANUAL_ENABLED', true),
            'instructions' => env('GATEWAY_MANUAL_INSTRUCTIONS', 'Send money to our bank or bKash personal number and enter the Transaction ID.'),
            'accounts' => [
                'bank' => [
                    'bank_name' => env('MANUAL_BANK_NAME', 'Dutch-Bangla Bank Ltd'),
                    'account_name' => env('MANUAL_ACCOUNT_NAME', 'Domain Reseller BD'),
                    'account_number' => env('MANUAL_ACCOUNT_NUMBER', '123.120.45678'),
                    'branch' => env('MANUAL_BANK_BRANCH', 'Dhaka'),
                ],
                'bkash_personal' => env('MANUAL_BKASH_PERSONAL', '01700000000'),
                'nagad_personal' => env('MANUAL_NAGAD_PERSONAL', '01800000000'),
            ],
        ],
        'bkash' => [
            'name' => 'bKash Tokenized Checkout',
            'enabled' => env('GATEWAY_BKASH_ENABLED', true),
            'app_key' => env('BKASH_APP_KEY'),
            'app_secret' => env('BKASH_APP_SECRET'),
            'username' => env('BKASH_USERNAME'),
            'password' => env('BKASH_PASSWORD'),
            'sandbox' => env('BKASH_SANDBOX', true),
            'currency' => 'BDT',
        ],
        'stripe' => [
            'name' => 'Stripe (Credit / Debit Card)',
            'enabled' => env('GATEWAY_STRIPE_ENABLED', true),
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => 'USD',
        ],
        'binance' => [
            'name' => 'Binance Pay (Crypto USDT)',
            'enabled' => env('GATEWAY_BINANCE_ENABLED', true),
            'api_key' => env('BINANCE_API_KEY'),
            'secret_key' => env('BINANCE_API_SECRET'),
            'merchant_id' => env('BINANCE_MERCHANT_ID'),
            'currency' => 'USDT',
        ],
    ],
];
