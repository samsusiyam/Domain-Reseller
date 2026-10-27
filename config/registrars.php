<?php

return [
    'default' => env('DEFAULT_REGISTRAR', 'namecheap'),

    'test_mode' => env('REGISTRAR_TEST_MODE', false),

    'low_balance_threshold' => env('REGISTRAR_LOW_BALANCE_THRESHOLD', 50.00),

    'modules' => [
        'namecheap' => [
            'name' => 'Namecheap',
            'api_user' => env('NAMECHEAP_API_USER'),
            'api_key' => env('NAMECHEAP_API_KEY'),
            'username' => env('NAMECHEAP_USERNAME', env('NAMECHEAP_API_USER')),
            'client_ip' => env('NAMECHEAP_CLIENT_IP'),
            'sandbox' => env('NAMECHEAP_SANDBOX', true),
            'timeout' => 30,
        ],
        'spaceship' => [
            'name' => 'Spaceship',
            'api_key' => env('SPACESHIP_API_KEY'),
            'api_secret' => env('SPACESHIP_API_SECRET'),
            'sandbox' => env('SPACESHIP_SANDBOX', true),
            'timeout' => 30,
        ],
        'dynadot' => [
            'name' => 'Dynadot',
            'api_key' => env('DYNADOT_API_KEY'),
            'sandbox' => env('DYNADOT_SANDBOX', true),
            'timeout' => 30,
        ],
        'atakdomain' => [
            'name' => 'AtakDomain',
            'api_key' => env('ATAKDOMAIN_API_KEY'),
            'api_secret' => env('ATAKDOMAIN_API_SECRET'),
            'sandbox' => env('ATAKDOMAIN_SANDBOX', true),
            'timeout' => 30,
        ],
        'namesilo' => [
            'name' => 'NameSilo',
            'api_key' => env('NAMESILO_API_KEY'),
            'sandbox' => env('NAMESILO_SANDBOX', true),
            'timeout' => 30,
        ],
        'cosmotown' => [
            'name' => 'Cosmotown',
            'api_key' => env('COSMOTOWN_API_KEY'),
            'api_secret' => env('COSMOTOWN_API_SECRET'),
            'sandbox' => env('COSMOTOWN_SANDBOX', true),
            'timeout' => 30,
        ],
    ],

    'default_nameservers' => [
        env('DEFAULT_NS1', 'ns1.hostnibo.com'),
        env('DEFAULT_NS2', 'ns2.hostnibo.com'),
    ],
];
