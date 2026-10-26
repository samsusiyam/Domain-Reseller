<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100 text-slate-900 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Reseller Portal' }} — {{ config('app.name', 'DomainReseller') }} Wholesale</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex overflow-hidden font-sans" x-data="{ sidebarOpen: false }">

    <!-- Mobile Backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"></div>

    <!-- Reseller Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-200 ease-in-out lg:static lg:translate-x-0">
        <!-- Logo Header -->
        <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-slate-950/60">
            <a href="{{ route('reseller.dashboard') }}" class="flex items-center gap-2.5 font-bold text-lg text-white">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white shadow-md">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="leading-tight">
                    <span class="block text-white font-extrabold tracking-tight">Reseller Portal</span>
                    <span class="block text-[10px] text-purple-400 font-mono uppercase tracking-wider">{{ strtoupper(auth()->user()->pricing_tier ?? 'Standard') }} Tier</span>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto text-xs font-semibold">
            <a href="{{ route('reseller.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('reseller.dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('reseller.pricing') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('reseller.pricing') ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Wholesale Pricing
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-widest text-slate-500 uppercase">API & Integrations</div>
            <a href="{{ route('reseller.api-keys.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reseller.api-keys.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                API Credentials
            </a>
            <a href="{{ route('reseller.api-docs') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reseller.api-docs') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                API & WHMCS Guide
            </a>
            <a href="{{ route('reseller.api-logs') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reseller.api-logs') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                API Access Logs
            </a>
            <a href="{{ route('reseller.webhooks.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reseller.webhooks.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Webhooks
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-widest text-slate-500 uppercase">Management</div>
            <a href="{{ route('reseller.sub-clients.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reseller.sub-clients.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Sub-Clients & Contacts
            </a>
            <a href="{{ route('reseller.whitelabel') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reseller.whitelabel') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                White-Label Setup
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-widest text-slate-500 uppercase">Analytics & Reports</div>
            <a href="{{ route('reseller.reports.domains') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reseller.reports.domains') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Domain Expiry Report
            </a>
            <a href="{{ route('reseller.reports.revenue') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reseller.reports.revenue') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Spending & Margins
            </a>
            <a href="{{ route('reseller.reports.api-usage') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reseller.reports.api-usage') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                API Usage Analytics
            </a>
        </nav>

        <!-- Reseller Footer Brief -->
        <div class="p-3 border-t border-slate-800 bg-slate-950/60">
            <div class="flex items-center justify-between">
                <div class="truncate">
                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()->company_name ?? auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Navigation Bar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 z-10 shadow-sm">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 lg:hidden">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-bold text-slate-900 truncate">{{ $header ?? 'Reseller Control Center' }}</h1>
            </div>

            <!-- Top Right Balance & Currency -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-purple-50 border border-purple-200 px-3.5 py-1.5 rounded-xl text-xs font-semibold">
                    <span class="text-purple-700">Wholesale Balance:</span>
                    <span class="text-purple-900 font-extrabold">${{ number_format((float)auth()->user()->balance, 2) }}</span>
                    @php
                        $bdt = app(\App\Services\Currency\CurrencyConverter::class)->convert((float)auth()->user()->balance, 'USD', 'BDT');
                    @endphp
                    <span class="text-purple-600">/ ৳{{ number_format($bdt, 2) }}</span>
                </div>

                <x-currency-selector />
            </div>
        </header>

        <!-- Page Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50/70">
            @if(session('success'))
                <div class="mb-6">
                    <x-alert type="success" :message="session('success')" />
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6">
                    <x-alert type="error" :message="session('error')" />
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

</body>
</html>
