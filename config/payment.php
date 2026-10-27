<?php

return [
    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'manual'),

    'gateways' => [
        'manual' => [
            'name' => 'Manual Payment / Bank Wire',
            'enabled' => env('GATEWAY_MANUAL_ENABLED', true),
        ],

        'bkash_personal' => [
            'name' => 'bKash Personal (Send Money)',
            'enabled' => env('GATEWAY_BKASH_PERSONAL_ENABLED', true),
            'personal_number' => env('BKASH_PERSONAL_NUMBER', '01849693630'),
        ],

        'bkash' => [
            'name' => 'bKash Merchant (Tokenized)',
            'enabled' => env('GATEWAY_BKASH_ENABLED', false),
            'app_key' => env('BKASH_APP_KEY', ''),
            'app_secret' => env('BKASH_APP_SECRET', ''),
            'username' => env('BKASH_USERNAME', ''),
            'password' => env('BKASH_PASSWORD', ''),
            'sandbox' => env('BKASH_SANDBOX', true),
        ],

        'sslcommerz' => [
            'name' => 'SSLCommerz (Cards / MFS / Net Banking)',
            'enabled' => env('GATEWAY_SSLCOMMERZ_ENABLED', false),
            'store_id' => env('SSLCOMMERZ_STORE_ID', ''),
            'store_passwd' => env('SSLCOMMERZ_STORE_PASSWD', ''),
            'sandbox' => env('SSLCOMMERZ_SANDBOX', true),
        ],

        'piprapay' => [
            'name' => 'PipraPay (Automated Multi-Channel)',
            'enabled' => env('GATEWAY_PIPRAPAY_ENABLED', false),
            'api_key' => env('PIPRAPAY_API_KEY', ''),
            'api_url' => env('PIPRAPAY_API_URL', 'https://piprapay.com/api'),
        ],

        'uddoktapay' => [
            'name' => 'UddoktaPay (bKash / Nagad / Rocket / Upay)',
            'enabled' => env('GATEWAY_UDDOKTAPAY_ENABLED', true),
            'api_key' => env('UDDOKTAPAY_API_KEY', ''),
            'api_url' => env('UDDOKTAPAY_API_URL', 'https://pay.hostnibo.com/api'),
        ],

        'binance' => [
            'name' => 'Binance Pay Merchant (USDT / Crypto)',
            'enabled' => env('GATEWAY_BINANCE_ENABLED', true),
            'api_key' => env('BINANCE_PAY_API_KEY', ''),
            'secret_key' => env('BINANCE_PAY_SECRET_KEY', ''),
            'merchant_id' => env('BINANCE_PAY_MERCHANT_ID', ''),
        ],

        'binance_personal' => [
            'name' => 'Binance Pay (Personal / Direct Pay ID)',
            'enabled' => env('GATEWAY_BINANCE_PERSONAL_ENABLED', true),
            'pay_id' => env('BINANCE_PERSONAL_PAY_ID', '123456789'),
            'email' => env('BINANCE_PERSONAL_EMAIL', 'payments@hostnibo.com'),
            'usdt_address' => env('BINANCE_PERSONAL_USDT_ADDRESS', ''),
            'network' => env('BINANCE_PERSONAL_NETWORK', 'TRC20 / BEP20'),
            'api_key' => env('BINANCE_PERSONAL_API_KEY', ''),
            'secret_key' => env('BINANCE_PERSONAL_SECRET_KEY', ''),
        ],

        'stripe' => [
            'name' => 'Credit / Debit Cards (Stripe)',
            'enabled' => env('GATEWAY_STRIPE_ENABLED', false),
            'key' => env('STRIPE_KEY', ''),
            'secret' => env('STRIPE_SECRET', ''),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        ],
    ],
];
