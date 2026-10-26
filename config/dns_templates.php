<?php

return [
    'templates' => [
        'google_workspace' => [
            'name' => 'Google Workspace (Gmail & Office)',
            'records' => [
                ['type' => 'MX', 'host' => '@', 'value' => 'aspmx.l.google.com', 'priority' => 1, 'ttl' => 3600],
                ['type' => 'MX', 'host' => '@', 'value' => 'alt1.aspmx.l.google.com', 'priority' => 5, 'ttl' => 3600],
                ['type' => 'MX', 'host' => '@', 'value' => 'alt2.aspmx.l.google.com', 'priority' => 5, 'ttl' => 3600],
                ['type' => 'MX', 'host' => '@', 'value' => 'alt3.aspmx.l.google.com', 'priority' => 10, 'ttl' => 3600],
                ['type' => 'MX', 'host' => '@', 'value' => 'alt4.aspmx.l.google.com', 'priority' => 10, 'ttl' => 3600],
                ['type' => 'TXT', 'host' => '@', 'value' => 'v=spf1 include:_spf.google.com ~all', 'priority' => null, 'ttl' => 3600],
            ],
        ],
        'microsoft_365' => [
            'name' => 'Microsoft 365 (Office 365 / Outlook)',
            'records' => [
                ['type' => 'MX', 'host' => '@', 'value' => '{domain-key}.mail.protection.outlook.com', 'priority' => 0, 'ttl' => 3600],
                ['type' => 'TXT', 'host' => '@', 'value' => 'v=spf1 include:spf.protection.outlook.com ~all', 'priority' => null, 'ttl' => 3600],
                ['type' => 'CNAME', 'host' => 'autodiscover', 'value' => 'autodiscover.outlook.com', 'priority' => null, 'ttl' => 3600],
            ],
        ],
        'cpanel_default' => [
            'name' => 'Standard cPanel / Shared Hosting',
            'records' => [
                ['type' => 'A', 'host' => '@', 'value' => '{server_ip}', 'priority' => null, 'ttl' => 14400],
                ['type' => 'CNAME', 'host' => 'www', 'value' => '@', 'priority' => null, 'ttl' => 14400],
                ['type' => 'CNAME', 'host' => 'mail', 'value' => '@', 'priority' => null, 'ttl' => 14400],
                ['type' => 'MX', 'host' => '@', 'value' => 'mail.{domain}', 'priority' => 0, 'ttl' => 14400],
                ['type' => 'TXT', 'host' => '@', 'value' => 'v=spf1 +a +mx +ip4:{server_ip} ~all', 'priority' => null, 'ttl' => 14400],
            ],
        ],
    ],
];
