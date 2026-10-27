@extends('layouts.admin', ['title' => 'Client #' . $client->id . ' — ' . $client->name, 'header' => 'Client Overview'])

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'summary' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.clients.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $client->name }}</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold
                        {{ $client->status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                        {{ ucfirst($client->status) }}
                    </span>
                    @if($client->isReseller())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                            Reseller
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Client #{{ $client->id }} &bull; {{ $client->email }} &bull; Member since {{ $client->created_at->format('M d, Y') }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Login As Client (1-Click Impersonation) -->
            <form action="{{ route('admin.clients.impersonate', $client->id) }}" method="POST">
                @csrf
                <button type="submit" class="cf-btn cf-btn-primary h-9 px-3.5 text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span>Login As {{ $client->isReseller() ? 'Reseller' : 'Client' }}</span>
                </button>
            </form>

            <a href="{{ route('admin.clients.edit', $client->id) }}" class="cf-btn cf-btn-secondary h-9 px-3 text-xs font-medium">
                Edit Details
            </a>

            <!-- Toggle Status -->
            <form action="{{ route('admin.clients.toggle-status', $client->id) }}" method="POST">
                @csrf
                <button type="submit" class="cf-btn cf-btn-secondary h-9 px-3 text-xs font-medium {{ $client->status === 'active' ? 'hover:text-red-600' : 'hover:text-emerald-600' }}">
                    {{ $client->status === 'active' ? 'Suspend Account' : 'Activate Account' }}
                </button>
            </form>
        </div>
    </div>

    <!-- WHMCS-style Navigation Tabs -->
    <div class="flex items-center gap-1 overflow-x-auto border-b border-slate-200 dark:border-slate-800 pb-2 text-xs font-medium tracking-tight">
        <button type="button" @click="activeTab = 'summary'"
                :class="activeTab === 'summary' ? 'border-b-2 border-[#F6821F] text-[#F6821F] font-semibold dark:text-[#F6821F]' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                class="px-3 py-1.5 transition-colors -mb-2.5">
            Summary & Profile
        </button>
        <button type="button" @click="activeTab = 'domains'"
                :class="activeTab === 'domains' ? 'border-b-2 border-[#F6821F] text-[#F6821F] font-semibold dark:text-[#F6821F]' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                class="px-3 py-1.5 transition-colors -mb-2.5 flex items-center gap-1.5">
            <span>Domains</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $client->domains_count ?? count($domains) }}</span>
        </button>
        <button type="button" @click="activeTab = 'orders'"
                :class="activeTab === 'orders' ? 'border-b-2 border-[#F6821F] text-[#F6821F] font-semibold dark:text-[#F6821F]' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                class="px-3 py-1.5 transition-colors -mb-2.5 flex items-center gap-1.5">
            <span>Orders</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ count($orders) }}</span>
        </button>
        <button type="button" @click="activeTab = 'invoices'"
                :class="activeTab === 'invoices' ? 'border-b-2 border-[#F6821F] text-[#F6821F] font-semibold dark:text-[#F6821F]' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                class="px-3 py-1.5 transition-colors -mb-2.5 flex items-center gap-1.5">
            <span>Invoices</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $client->invoices_count ?? count($invoices) }}</span>
        </button>
        <button type="button" @click="activeTab = 'transactions'"
                :class="activeTab === 'transactions' ? 'border-b-2 border-[#F6821F] text-[#F6821F] font-semibold dark:text-[#F6821F]' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                class="px-3 py-1.5 transition-colors -mb-2.5">
            Transactions (Ledger)
        </button>
        <button type="button" @click="activeTab = 'tickets'"
                :class="activeTab === 'tickets' ? 'border-b-2 border-[#F6821F] text-[#F6821F] font-semibold dark:text-[#F6821F]' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                class="px-3 py-1.5 transition-colors -mb-2.5 flex items-center gap-1.5">
            <span>Tickets</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ count($tickets) }}</span>
        </button>
    </div>

    <!-- TAB 1: SUMMARY & PROFILE -->
    <div x-show="activeTab === 'summary'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Info Card -->
            <div class="lg:col-span-2 bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4 uppercase tracking-wider">
                    Client Details
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Full Name</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $client->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Email Address</span>
                        <span class="font-mono text-slate-900 dark:text-slate-100 text-xs">{{ $client->email }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Company</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $client->company_name ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Phone</span>
                        <span class="font-mono text-slate-900 dark:text-slate-100 text-xs">{{ $client->phone ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Country</span>
                        <span class="font-semibold text-slate-900 dark:text-slate-100 text-xs uppercase">{{ $client->country ?: 'BD' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Email Status</span>
                        <span class="text-xs font-semibold {{ $client->email_verified_at ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-500' }}">
                            {{ $client->email_verified_at ? '✓ Verified' : '⚠ Unverified' }}
                        </span>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <span class="text-slate-400 block font-semibold uppercase text-[10px]">Address</span>
                        <span class="text-slate-700 dark:text-slate-300 text-xs">
                            {{ $client->address_1 ?: 'No address specified' }} {{ $client->city ? '• ' . $client->city : '' }} {{ $client->state ? ', ' . $client->state : '' }} {{ $client->postal_code ? '(' . $client->postal_code . ')' : '' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Wallet Balance Card & Adjustment -->
            <div class="lg:col-span-1 bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 flex flex-col justify-between shadow-xs">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Account Balance</span>
                    <div class="text-3xl font-bold font-mono text-slate-900 dark:text-white mt-1 tabular-nums">
                        ${{ number_format($client->walletBalance, 2) }}
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Available credit for registrations & renewals.</p>
                </div>

                <!-- Balance Adjustment Form -->
                <form action="{{ route('admin.clients.adjust-balance', $client->id) }}" method="POST" class="mt-6 border-t border-slate-100 dark:border-slate-800 pt-4 space-y-3">
                    @csrf
                    <span class="text-xs font-semibold text-slate-900 dark:text-slate-100 block">Manual Adjustment</span>
                    <div class="flex gap-2">
                        <select name="type" required class="cf-input text-xs w-1/3">
                            <option value="credit">Credit (+)</option>
                            <option value="debit">Debit (-)</option>
                        </select>
                        <input type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required class="cf-input text-xs w-2/3 font-mono">
                    </div>
                    <input type="text" name="description" placeholder="Adjustment reason / note..." required class="cf-input text-xs w-full">
                    <button type="submit" class="cf-btn cf-btn-secondary w-full text-xs">Apply Balance Adjustment</button>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB 2: DOMAINS -->
    <div x-show="activeTab === 'domains'" class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Assigned Domain Names</h3>
            <span class="text-xs text-slate-400">{{ count($domains) }} domain(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-slate-500 uppercase text-[11px] font-semibold">
                        <th class="py-3 px-4">Domain</th>
                        <th class="py-3 px-4">Registrar</th>
                        <th class="py-3 px-4">Expiry Date</th>
                        <th class="py-3 px-4">Auto-Renew</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($domains as $d)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                <a href="{{ route('admin.domains.show', $d->id) }}" class="hover:text-[#F6821F]">
                                    {{ $d->domain_name }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-mono">{{ $d->registrarModule?->display_name ?? $d->registrar ?? '—' }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-mono">{{ $d->expires_at ? $d->expires_at->format('Y-m-d') : '—' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium {{ $d->auto_renew ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                    {{ $d->auto_renew ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $d->status === 'active' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600' }}">
                                    {{ $d->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.domains.show', $d->id) }}" class="cf-btn cf-btn-secondary text-[11px] py-1 px-2.5">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No domains registered under this account yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: ORDERS -->
    <div x-show="activeTab === 'orders'" class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Domain Orders</h3>
            <a href="{{ route('admin.orders.create') . '?user_id=' . $client->id }}" class="cf-btn cf-btn-primary text-xs py-1 px-2.5">
                + New Order
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-slate-500 uppercase text-[11px] font-semibold">
                        <th class="py-3 px-4">Order #</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($orders as $o)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3 px-4 font-mono font-semibold text-slate-900 dark:text-white">
                                <a href="{{ route('admin.orders.show', $o->id) }}" class="hover:text-[#F6821F]">
                                    {{ $o->order_number }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-900 dark:text-white">
                                ${{ number_format($o->total_amount, 2) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $o->status === 'completed' ? 'bg-emerald-500/10 text-emerald-600' : ($o->status === 'failed' ? 'bg-red-500/10 text-red-600' : 'bg-amber-500/10 text-amber-600') }}">
                                    {{ $o->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $o->created_at->format('M d, Y H:i') }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.orders.show', $o->id) }}" class="cf-btn cf-btn-secondary text-[11px] py-1 px-2.5">
                                    View Order
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No orders found for this client.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: INVOICES -->
    <div x-show="activeTab === 'invoices'" class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Invoices & Billing</h3>
            <a href="{{ route('admin.invoices.create') . '?user_id=' . $client->id }}" class="cf-btn cf-btn-primary text-xs py-1 px-2.5">
                + New Invoice
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-slate-500 uppercase text-[11px] font-semibold">
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Balance Due</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3 px-4 font-mono font-semibold text-slate-900 dark:text-white">
                                <a href="{{ route('admin.invoices.show', $inv->id) }}" class="hover:text-[#F6821F]">
                                    #{{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-900 dark:text-white">${{ number_format($inv->total_amount, 2) }}</td>
                            <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">${{ number_format($inv->balance_due, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $inv->status === 'paid' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-red-500/10 text-red-600' }}">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $inv->due_date ? $inv->due_date->format('M d, Y') : '—' }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.invoices.show', $inv->id) }}" class="cf-btn cf-btn-secondary text-[11px] py-1 px-2.5">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No invoices issued for this client.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 5: TRANSACTIONS -->
    <div x-show="activeTab === 'transactions'" class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Account Ledger & Transactions</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-slate-500 uppercase text-[11px] font-semibold">
                        <th class="py-3 px-4">Reference</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Gateway</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($transactions as $txn)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3 px-4 font-mono text-slate-900 dark:text-slate-100">{{ $txn->reference_id ?? $txn->gateway_transaction_id ?? ('#TXN-' . $txn->id) }}</td>
                            <td class="py-3 px-4 uppercase font-semibold text-[10px] {{ $txn->type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">{{ $txn->type }}</td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300 capitalize">{{ $txn->gateway }}</td>
                            <td class="py-3 px-4 font-mono font-semibold {{ $txn->type === 'credit' ? 'text-emerald-600' : 'text-slate-900 dark:text-white' }}">
                                {{ $txn->type === 'credit' ? '+' : '-' }}${{ number_format($txn->amount, 2) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-emerald-500/10 text-emerald-600">
                                    {{ $txn->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No transactions recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 6: TICKETS -->
    <div x-show="activeTab === 'tickets'" class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Support Tickets</h3>
            <a href="{{ route('admin.tickets.create') . '?user_id=' . $client->id }}" class="cf-btn cf-btn-primary text-xs py-1 px-2.5">
                + Open Ticket
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 text-slate-500 uppercase text-[11px] font-semibold">
                        <th class="py-3 px-4">Ticket #</th>
                        <th class="py-3 px-4">Department</th>
                        <th class="py-3 px-4">Subject</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Last Activity</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($tickets as $t)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3 px-4 font-mono font-semibold text-slate-900 dark:text-white">
                                <a href="{{ route('admin.tickets.show', $t->id) }}" class="hover:text-[#F6821F]">
                                    #{{ $t->ticket_number }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300 capitalize">{{ $t->department }}</td>
                            <td class="py-3 px-4 text-slate-900 dark:text-slate-100 font-medium truncate max-w-xs">{{ $t->subject }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $t->status === 'open' ? 'bg-amber-500/10 text-amber-600' : 'bg-emerald-500/10 text-emerald-600' }}">
                                    {{ $t->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $t->updated_at->diffForHumans() }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.tickets.show', $t->id) }}" class="cf-btn cf-btn-secondary text-[11px] py-1 px-2.5">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No support tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
