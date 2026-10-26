<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KnowledgebaseController extends Controller
{
    /**
     * Show knowledgebase homepage.
     */
    public function index()
    {
        $categories = [
            [
                'title' => 'Domain Management',
                'description' => 'Guides on managing DNS, nameservers, EPP codes, and renewals.',
                'slug' => 'domain-management',
                'articles_count' => 8,
            ],
            [
                'title' => 'Billing & Payments',
                'description' => 'Information on wallet deposits, bKash, Stripe, Binance, and invoicing.',
                'slug' => 'billing-payments',
                'articles_count' => 6,
            ],
            [
                'title' => 'Reseller API & WHMCS',
                'description' => 'Integration guides for WHMCS module, REST API authentication, and webhooks.',
                'slug' => 'reseller-api',
                'articles_count' => 10,
            ],
            [
                'title' => 'Security & 2FA',
                'description' => 'Securing your account with Two-Factor Authentication and API IP whitelisting.',
                'slug' => 'security',
                'articles_count' => 4,
            ],
        ];

        return view('knowledgebase.index', compact('categories'));
    }

    /**
     * Show single knowledgebase category or article.
     */
    public function show(string $slug)
    {
        return view('knowledgebase.show', compact('slug'));
    }
}
